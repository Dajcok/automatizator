FROM tomcat:10.0

RUN wget https://github.com/orbeon/orbeon-forms/releases/download/tag-release-2023.1-ce/orbeon-2023.1.202312312000-CE.zip

RUN wget https://jdbc.postgresql.org/download/postgresql-42.2.23.jar -P /usr/local/tomcat/lib/

RUN apt-get update && apt-get install -y unzip procps

RUN unzip orbeon-2023.1.202312312000-CE.zip

RUN mv orbeon-2023.1.202312312000-CE/orbeon.war /usr/local/tomcat/webapps/

RUN rm -rf orbeon-2023.1.202312312000-CE.zip orbeon-2023.1.202312312000-CE

ENV JAVA_OPTS="-Doxf.run.mode=local -Doxf.properties.file=/usr/local/tomcat/webapps/orbeon/WEB-INF/resources/config/properties-local.xml"

# Spustenie Tomcatu, aby sa rozbalil war súbor. Musíme to robiť manuálne, inak by bind v
# docker-compose na config vytvoril orbeon folder skor a tomcat by ho potom nevedel rozbaliť
RUN catalina.sh run & sleep 30 && pkill -f 'org.apache.catalina.startup.Bootstrap'

RUN mkdir -p /usr/local/logs && touch /usr/local/logs/orbeon.log

EXPOSE 8080

COPY docker-startup.sh /usr/local/tomcat/bin/

RUN chmod +x /usr/local/tomcat/bin/docker-startup.sh

CMD ["/usr/local/tomcat/bin/docker-startup.sh"]
