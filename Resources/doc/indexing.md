# How to index more than 500k entities

If you want to index a lot of entities then it is not a good idea to use `solr:index:populate`. 
The command works well with 100k-200k entites everything larger than that makes the command incredible slow and needs a lot of memory. This is because doctrine is not designed to handle hundred-thousands of entities.

In my following example I have a `person` table with 5000000 rows and three columns: `id`, `name` and `email`. The resulting documents are schemaless, so all fields have a suffix e.g. `name_s`.

Here are some possibilities which works well for me:

## MySQL Prepared Statement + Solr PostTool

This solution does not use PHP.

1. export your data to person.csv
```sql
SET @TS = DATE_FORMAT(NOW(),'_%Y_%m_%d_%H_%i_%s');

SET @FOLDER = '/tmp/'; -- target dir
SET @PREFIX = 'person';
SET @EXT    = '.csv';

-- first select defines the header of the csv-file
SET @CMD = CONCAT("SELECT 'id', 'name_s', 'email_s' UNION ALL SELECT * FROM person INTO OUTFILE '",@FOLDER,@PREFIX,@TS,@EXT,
    "' FIELDS ENCLOSED BY '\"' TERMINATED BY ',' ESCAPED BY '\"'",
    "  LINES TERMINATED BY '\r\n';");

PREPARE statement FROM @CMD;

EXECUTE statement;
```

Then run this SQL-script:

```bash
mysql -udbuser -p123 dbname < dump_person_table.sql
```

The resulting file looks like this: `/tmp/person_2017_03_01_11_21_41.csv`

2. index the csv with [post-tool](https://lucidworks.com/2015/08/04/solr-5-new-binpost-utility/)

```bash
/opt/solr/solr-5.5.2/bin/post -c core0 /tmp/person_2017_03_01_11_21_41.csv
```

## PDO Select + [Solarium BufferedAdd](http://solarium.readthedocs.io/en/stable/plugins/#example-usage)

The script has two parts:
1. select a chunk of rows from the DB
2. add the rows to the index with Solarium

```php
<?php
require 'vendor/autoload.php';

$connection = new PDO('mysql:host=localhost;dbname=dbname;charset=utf8mb4', 'dbuser', '123');
$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$statement = $connection->prepare('SELECT COUNT(*) as total_items FROM person');
$statement->execute();
$countResult = $statement->fetch(PDO::FETCH_ASSOC);

$totalItems = $countResult['total_items'];
$batchSize = 5000;

$pages = ceil($totalItems / $batchSize);

$client = new Solarium\Client([
    'endpoint' => [
        'localhost' => [
            'host' => 'localhost',
            'port' => 8983,
            'path' => '/solr/core0',
        ]
    ]
]);

/** @var \Solarium\Plugin\BufferedAdd\BufferedAdd $buffer */
$buffer = $client->getPlugin('bufferedadd');
$buffer->setBufferSize($batchSize);

for ($i = 0; $i <= $pages; $i++) {
    $statement = $connection->prepare(sprintf('SELECT id, name, email FROM person LIMIT %s, %s', $i * $batchSize, $batchSize));
    $statement->execute();

    foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item) {
        $buffer->createDocument([
            'id' => $item['id'],
            'name_s' => $item['name'],
            'email_s' => $item['email']
        ]);
    }

    $statement->closeCursor();

    $buffer->commit();

    echo sprintf('Indexing page %s / %s', $i, $pages) . PHP_EOL;
}

$buffer->flush();```
