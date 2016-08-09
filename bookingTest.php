<?php
require('booking.php');
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase {
    var $booking;
    protected function setUp() {
        // truncate table
        $db = new PDO('mysql:host=localhost;port=3306;dbname=tennis-booking;charset=UTF8;', 'root', 'Administrator');
        $db->exec('truncate booking');
        
        $this->booking = new Booking();
    }
    
    public function testDuplicate() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        // database is empty, it should be no duplicate
        $this->assertFalse($this->booking->isDuplicateBooking($todayFormat,'suhendro.mail@gmail.com'));
    }
    
    public function testTimeSlot() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        // add booking before business hour start
        $this->assertFalse($this->booking->isSlotAvailable($todayFormat, '08:59:00', '09:59:00'));
        
        // add booking one hour before business hour end
        $this->assertFalse($this->booking->isSlotAvailable($todayFormat, '20:01:00', '21:01:00'));
        
        // add booking in business hour
        $this->assertTrue($this->booking->isSlotAvailable($todayFormat, '09:00:00', '10:00:00'));
        $this->assertTrue($this->booking->isSlotAvailable($todayFormat, '20:00:00', '21:00:00'));
    }
    
    public function testGetAllBooking() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        $book = array('name' => 'Suhendro', 'email' => 'suhendro.mail@gmail.com', 'schedule' => $todayFormat, 'start' => '10:00:00', 'end' => '11:00:00');
        $this->booking->addBooking($book);
        $this->assertEquals(1, count($this->booking->getAllBooking()));
        
        $book1 = array('name' => 'test', 'email' => 'test@mail.com', 'schedule' => $todayFormat, 'start' => '09:00:00', 'end' => '10:00:00');
        $this->booking->addBooking($book1);
        $this->assertEquals(2, count($this->booking->getAllBooking()));
        
        $book2 = array('name' => 'test1', 'email' => 'test1@mail.com', 'schedule' => $todayFormat, 'start' => '12:30:00', 'end' => '13:30:00');
        $this->booking->addBooking($book2);
        $this->assertEquals(3, count($this->booking->getAllBooking()));
    }
    
    public function testGetBooking() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        $book = array('name' => 'Suhendro', 'email' => 'suhendro.mail@gmail.com', 'schedule' => $todayFormat, 'start' => '10:00:00', 'end' => '11:00:00');
        $this->booking->addBooking($book);
        
        $books = $this->booking->getAllBooking();
        $theBook = $this->booking->getBooking($books[0]['id']);
        $this->assertEquals($books[0]['email'], $theBook['email']);
    }
    
    /**
     * @expectedException Exception
     */
    public function testAddBooking() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        $book = array('name' => 'Suhendro', 'email' => 'suhendro.mail@gmail.com', 'schedule' => $todayFormat, 'start' => '10:00:00', 'end' => '11:00:00');
        $this->assertTrue($this->booking->addBooking($book));
        $this->assertInstanceOf(Exception::class, $this->booking->addBooking($book));
        
        $book1 = array('name' => 'test', 'email' => 'test@mail.com', 'schedule' => $todayFormat, 'start' => '09:30:00', 'end' => '10:30:00');
        $this->assertInstanceOf(Exception::class, $this->booking->addBooking($book1));
        
        $book2 = array('name' => 'test1', 'email' => 'test1@mail.com', 'schedule' => $todayFormat, 'start' => '10:30:00', 'end' => '11:30:00');
        $this->assertInstanceOf(Exception::class, $this->booking->addBooking($book2));
    }
    
    public function testUpdateBooking() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        $book3 = array('name' => 'test3', 'email' => 'test3@mail.com', 'schedule' => $todayFormat, 'start' => '12:00:00', 'end' => '13:00:00');
        $this->booking->addBooking($book3);
        
        $books = $this->booking->getAllBooking();
        
        $books[0]['start'] = '12:30:00';
        $books[0]['end'] = '13:30:00';
        $this->assertTrue($this->booking->updateBooking($books[0]));
    }
    
    public function testDeleteBooking() {
        $today = new DateTime();
        $todayFormat = $today->format('Y-m-d');
        
        $book = array('name' => 'Suhendro', 'email' => 'suhendro.mail@gmail.com', 'schedule' => $todayFormat, 'start' => '10:00:00', 'end' => '11:00:00');
        $this->booking->addBooking($book);
        
        $books = $this->booking->getAllBooking();
        $this->booking->deleteBooking($books[0]['id']);
        $this->assertFalse($this->booking->getBooking($books[0]['id']));
    }
}

?>