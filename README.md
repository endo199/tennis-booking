# Tennis Booking Apps
Simple web apps for booking tennis stadium for one hour per user between 09:00 until 20:00

# Setup
* Install database
** table

```
CREATE TABLE `booking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `email` varchar(45) NOT NULL,
  `schedule` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```
** update database connection in booking.php

* Run the server with this folder as main directory, then access index.html