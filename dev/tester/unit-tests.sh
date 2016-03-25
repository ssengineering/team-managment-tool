/bin/bash
until nc -z mongodb 27017; do
 echo "$(date) - waiting for mongo to be ready"
 sleep 1
done

until nc -z db 3306; do
 echo "$(date) - waiting for mysql to be ready"
 sleep 1
done

echo "Starting testing db dump"
echo 'SET FOREIGN_KEY_CHECKS=0; DROP DATABASE IF EXISTS `sites_ops_test`;' \
	| mysql -h db -u root -pdevteam
cat /tmp/test/testDB.sql | mysql -h db -u root -pdevteam
echo "Testing DB dumped"

echo "Starting unit tests"
phpunit --bootstrap /tmp/test/autoload.php /tmp/test/unit/ >> /tmp/test-results.txt
echo "Unit tests finished"
