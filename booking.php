<?php
    class Booking {
        private $db;
        
        function __construct() {
            date_default_timezone_set('Asia/Jakarta');
            try {
                
                $this->db = new PDO('mysql:host=localhost;port=3306;dbname=tennis-booking;charset=UTF8;', 'root', 'Administrator');
                
            } catch(PDOException $e) {
                die($e->getMessage());
            }
        }
        
        /**
            check whether requested time slot is available within business hour and not collide with other slot
        */
        function isSlotAvailable($date, $start, $end, $id = null) {
            // FIRST GAME START
            $firstTime = DateTime::createFromFormat('H:i:s', "09:00:00");
            // LAST GAME START
            $lastTime = DateTime::createFromFormat('H:i:s', "20:00:00");
            
            $startTime = DateTime::createFromFormat('H:i:s', $start);
            
            if( $startTime < $firstTime || $lastTime < $startTime) {
                // out side work hours
                return false;
            }
            
            if($id) {
                $avail = $this->db->prepare('select count(id) from booking where schedule = ? and not(end <= ? or `start` >= ?) and id != ?');
                $avail->execute(array($date, $start, $end, $id));
            } else {
                $avail = $this->db->prepare('select count(id) from booking where schedule = ? and not(end <= ? or `start` >= ?)');
                $avail->execute(array($date, $start, $end));
            }
            
            $result = intval($avail->fetchColumn());
            
            return $result == 0;
        }
        
        /**
            one user only can book once, based on email
        */
        function isDuplicateBooking($date, $email) {
            $query = $this->db->prepare('select count(id) from booking where schedule = ? and email = ?');
            $query->execute(array($date, $email));
            $numOfBooking = intval($query->fetchColumn());
            
            return $numOfBooking != 0;
        }
        
        /**
            add booking
            @return true if success or Exception if there is error
        */
        function addBooking(array $book) {
            if($this->isDuplicateBooking($book['schedule'], $book['email'])) {
                throw new Exception('Duplicate booking, you have a booking with this email');
            }
            
            if($this->isSlotAvailable($book['schedule'], $book['start'], $book['end'])) {
                $insert = $this->db->prepare('insert into booking (name, email, schedule, `start`, end) values (:name, :email, :schedule, :start, :end)');
                
                $insert->bindParam(':name', $book['name'], PDO::PARAM_STR);
                $insert->bindParam(':email', $book['email'], PDO::PARAM_STR);
                $insert->bindParam(':schedule', $book['schedule'], PDO::PARAM_STR);
                $insert->bindParam(':start', $book['start'], PDO::PARAM_STR);
                $insert->bindParam(':end', $book['end'], PDO::PARAM_STR);
                
                $success = $insert->execute();
                if(!$success) {
                    throw new Exception($insert->errorInfo());
                }
                return $success;
            } else {
                throw new Exception('Slot time is not available');
            }
        }
        
        /**
            update existing booking, only name, start time, and end time can be updated
            @return true if success or Exception other wise
        */
        function updateBooking(array $book) {
            if($this->isSlotAvailable($book['schedule'], $book['start'], $book['end'], $book['id'])) {
                $update = $this->db->prepare('update booking set name = ?, `start` = ?, end = ? where id = ?');
                $success = $update->execute(array($book['name'], $book['start'], $book['end'], $book['id']));
                
                if(!$success) {
                    throw new Exception($update->errorInfo());
                }
                return $success;
            }
            
            throw new Exception('Slot time is not available');
        }
        
        /**
            delete a booking based on given id
            @return number of effected rows
        */
        function deleteBooking($bookingId) {
            return $this->db->exec('delete from booking where id = '.$bookingId);
        }
        
        /**
            get a booking based on given id
            @return the booking
        */
        function getBooking($bookingId) {
            return $this->db->query('select * from booking where id = '.$bookingId, PDO::FETCH_ASSOC)->fetch();
        }
        
        /**
            get all booking for today
            @return todays bookings
        */
        function getAllBooking() {
            $today = new DateTime();
            $tgl = $today->format('Y-m-d');
            
            
            $result = $this->db->query('select * from booking where schedule = "'.$tgl.'" order by `start`', PDO::FETCH_ASSOC);
            return $result->fetchAll();
        }
    }
?>