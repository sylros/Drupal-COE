RabbitMQ docker image

 - pull RabbitMQ docker image from DockerHub
 docker pull rabbitmq:3-management
 
 - start RabbitMQ container
 docker run -d --name cops-rabbit -p 5672:5672 -p 15672:15672 rabbitmq:3-management
 
 - install RabbitMQ Drupal module:
 
   inside the Drupal container run
   composer require drupal/rabbitmq
   
   install php extention sockets 
   docker-php-ext-install sockets
   
   install php-amqplib
   composer require php-amqplib/php-amqplib

 - restart Drupal container
 
 
Docker containers start:

docker start drupal-cops-data
docker start drupal-cops
docker start smtpserver-cops
docker start cops-rabbit

Update the PHP docker container with the following commands

apt-get update
apt-get install unzip
docker-php-ext-install zip
docker-php-ext-install bcmath

To fix the issue with connecting to rabbitmq container change the localhost in settings.php to container IP address. Run {docker inspect cops-rabbit} to find IP address ("IPAddress": "172.17.0.5").
$settings['rabbitmq_credentials']['default'] = [
  'host' => '172.17.0.5',
  'port' => 5672,
  'vhost' => '/',
  'username' => 'guest',
  'password' => 'guest',
];
$settings['queue_service_queue1'] = 'queue.rabbitmq';

RabbitMQ management interface: http://0.0.0.0:15672 (or localhost:15672)

