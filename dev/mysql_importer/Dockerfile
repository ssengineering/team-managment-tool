FROM mysql

RUN apt-get update
RUN apt-get install curl -y
RUN apt-get install netcat -y

ADD import.sh /tmp/import.sh
ADD microservices.sql /tmp/microservices.sql
RUN chmod +x /tmp/import.sh

CMD /tmp/import.sh
