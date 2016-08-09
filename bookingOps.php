<?php
// double booking
    require('booking.php');

    $booking = new Booking();

    
    if(isset($_REQUEST['booking'])) {
        $book = $_REQUEST['booking'];
        $today = new DateTime();
        $book['schedule'] = $today->format('Y-m-d');

        $startTime = DateTime::createFromFormat('H:i', $book['start']);
        $book['start'] = $startTime->format('H:i:s');

        $endTime = $startTime->add(new DateInterval('PT1H')); // add one hour
        $book['end'] = $endTime->format('H:i:s');
    }

    $ops = $_REQUEST['ops'];
    switch($ops) {
        case 'insert':
//            var_dump($book);
            try {
                $result = $booking->addBooking($book);
                if($result) {
                    print json_encode(array('status' => true));
                } else {
                    print json_encode(array('status' => false, 'message' => 'Error adding new booking'));
                }
            } catch(Exception $e) {
                print json_encode(array('status' => false, 'message' => $e->getMessage()));
            }
            break;
        case 'update':
            try {
                $result = $booking->updateBooking($book);
                if($result === false) {
                    print json_encode(array('status' => false, 'message' => 'Error updating booking'));
                } else {
                    print json_encode(array('status' => true));
                }
            } catch(Exception $e) {
                print json_encode(array('status' => false, 'message' => $e->getMessage()));
            }
            break;
        case 'delete':
            $result = $booking->deleteBooking($_REQUEST['id']);
            if($result === false) {
                print json_encode(array('status' => false, 'message' => 'Error deleting a booking'));
            } else {
                print json_encode(array('status' => true));
            }
            break;
        case 'get':
            print json_encode($booking->getBooking($_REQUEST['id']));
            break;
        default:
            print json_encode($booking->getAllBooking());
    }
?>