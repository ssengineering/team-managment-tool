#!/bin/bash
until nc -z db 3306; do
  echo "$(date) - waiting for mysql to be ready"
  sleep 1
done

curl -L $DB_IMPORT_URL$DB_IMPORT_TOKEN | mysql -h db -u root -p$DB_ENV_MYSQL_ROOT_PASSWORD $DB_ENV_MYSQL_DATABASE

echo "CREATE USER 'devteam'@'%';" | mysql -h db -u root -p$DB_ENV_MYSQL_ROOT_PASSWORD $DB_ENV_MYSQL_DATABASE
echo "GRANT ALL ON $DB_ENV_MYSQL_DATABASE.* TO 'devteam'@'%';" | mysql -h db -u root -p$DB_ENV_MYSQL_ROOT_PASSWORD $DB_ENV_MYSQL_DATABASE

echo "source /tmp/microservices.sql" | mysql -h db -u root -p$DB_ENV_MYSQL_ROOT_PASSWORD $DB_ENV_MYSQL_DATABASE
