<?php
namespace Edu\Cnm\DataDesign\Test;

use Edu\Cnm\DataDesign\{Profile, Tweet, Image};

// grab the class under scrutiny
require_once(dirname(__DIR__) . "/autoload.php");

// grab the uuid generator
require_once(dirname(__DIR__, 2) . "/lib/uuid.php");

/**
 * Full PHPUnit test for the Image class
 *
 * This is a complete PHPUnit test of the Image class. It is complete because *ALL* mySQL/PDO enabled methods
 * are tested for both invalid and valid inputs.
 *
 * @see Image
 * @author Marty Bonacci <mbonacci@cnm.edu>
 * @modeled after TweetTest.php by Dylan McDonald <dmcdonald21@cnm.edu>
 **/
class ImageTest extends DataDesignTest {
	/**
	 * Profile that created the liked the Tweet; this is for foreign key relations
	 * @var  Profile $profile
	 **/
	protected $profile = null;

	/**
	 * Tweet that was liked; this is for foreign key relations
	 * @var Tweet $tweet
	 **/
	protected $tweet = null;

	/**
	 * valid hash to use
	 * @var $VALID_HASH
	 */
	protected $VALID_HASH;

	/**
	 * timestamp of the Like; this starts as null and is assigned later
	 * @var \DateTime $VALID_LIKEDATE
	 **/
	protected $VALID_LIKEDATE;

	/**
	 * valid salt to use to create the profile object to own the test
	 * @var string $VALID_SALT
	 */
	protected $VALID_SALT;

	/**
	 * valid activationToken to create the profile object to own the test
	 * @var string $VALID_ACTIVATION
	 */
	protected $VALID_ACTIVATION;


	/**
	 * valid IMAGECLOUDINARYTOKEN to create the image object to own the test
	 * @var string $VALID_IMAGECLOUDINARYTOKEN
	 */
	protected $VALID_IMAGECLOUDINARYTOKEN;


	/**
	 * valid IMAGEURL to create the image object to own the test
	 * @var string $VALID_IMAGEURL
	 */
	protected $VALID_IMAGEURL;




	/**
	 * create dependent objects before running each test
	 **/
	public final function setUp() : void {
		// run the default setUp() method first
		parent::setUp();

		// create a salt and hash for the mocked profile
		$password = "abc123";
		$this->VALID_SALT = bin2hex(random_bytes(32));
		$this->VALID_HASH = hash_pbkdf2("sha512", $password, $this->VALID_SALT, 262144);
		$this->VALID_ACTIVATION = bin2hex(random_bytes(16));

		// create and insert the mocked profile
		$this->profile = new Profile(generateUuidV4(), null,"@phpunit", "test@phpunit.de",$this->VALID_HASH, "+12125551212", $this->VALID_SALT);
		$this->profile->insert($this->getPDO());

		// create the and insert the mocked tweet
		$this->tweet = new Tweet(generateUuidV4(), $this->profile->getProfileId(), "PHPUnit like test passing");
		$this->tweet->insert($this->getPDO());

		// calculate the date (just use the time the unit test was setup...)
		$this->VALID_LIKEDATE = new \DateTime();
	}

	/**
	 * test inserting a valid Image and verify that the actual mySQL data matches
	 **/
	public function testInsertValidImage() : void {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// create a new Image and insert to into mySQL
		$imageId = generateUuidV4();
		$image = new Image($imageId, $this->tweet->getTweetId(),$this->VALID_IMAGECLOUDINARYTOKEN, $this->VALID_IMAGEURL);
		$image->insert($this->getPDO());

		// grab the data from mySQL and enforce the fields match our expectations
		$pdoImage = Image::getImageByImageId($this->getPDO(), $image->getImageId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$this->assertEquals($pdoImage->getImageId(), $imageId);
		$this->assertEquals($pdoImage->getImageTweetId(), $this->tweet->getTweetId());
		$this->assertEquals($pdoImage->getImageCloudinaryToken(), $this->VALID_IMAGECLOUDINARYTOKEN);
		$this->assertEquals($pdoImage->getImageUrl(), $this->VALID_IMAGEURL);
	}
	/**
	 * test creating an Image and then deleting it
	 **/
	public function testDeleteValidImage() : void {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// create a new Image and insert to into mySQL
		$imageId = generateUuidV4();
		$image = new Image($imageId, $this->tweet->getTweetId(),$this->VALID_IMAGECLOUDINARYTOKEN, $this->VALID_IMAGEURL);
		$image->insert($this->getPDO());

		// delete the Image from mySQL
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
		$image->delete($this->getPDO());

		// grab the data from mySQL and enforce the Image does not exist
		$pdoLike = Image::getImageByImageId($this->getPDO(), $this->	image->getImageId());
		$this->assertNull($pdoLike);
		$this->assertEquals($numRows, $this->getConnection()->getRowCount("image"));
	}



	/**
	 * test grabbing a Image that does not exist
	 **/
	public function testGetImageByImageId() {
		// grab a tweet id and profile id that exceeds the maximum allowable tweet id and profile id
		$image = Like::getImageByImageId($this->getPDO(), generateUuidV4());
		$this->assertNull($image);
	}

	/**
	 * test grabbing an Image by tweet id
	 **/
	public function testGetValidImageByTweetId() : void {
		// count the number of rows and save it for later
		$numRows = $this->getConnection()->getRowCount("image");

		// create a new Image and insert to into mySQL
		$imageId = generateUuidV4();
		$image = new Image($imageId, $this->tweet->getTweetId(),$this->VALID_IMAGECLOUDINARYTOKEN, $this->VALID_IMAGEURL);
		$image->insert($this->getPDO());

		// grab the data from mySQL and enforce the fields match our expectations
		$pdoImage = Image::getImageByImageTweetId($this->getPDO(), $this->tweet->getTweetId());
		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));

		// enforce no other objects are bleeding into the test
		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\DataDesign\\Like", $pdoImage );
	}

	/**
	 * test grabbing a Image by a tweet id that does not exist
	 **/
	public function testGetInvalidImageByTweetId() : void {
		// grab a tweet id that exceeds the maximum allowable tweet id
		$image = Image::getImageByImageTweetId($this->getPDO(), generateUuidV4());
		$this->assertCount(0, $image);
	}

//	/**
//	 * test grabbing an Image by profile id
//	 **/
//	public function testGetValidImageByProfileId() : void {
//		// count the number of rows and save it for later
//		$numRows = $this->getConnection()->getRowCount("image");
//
//		// create a new Image and insert to into mySQL
//		$imageId = generateUuidV4();
//		$image = new Image($imageId, $this->tweet->getTweetId(),$this->VALID_IMAGECLOUDINARYTOKEN, $this->VALID_IMAGEURL);
//		$image->insert($this->getPDO());
//
//		// grab the data from mySQL and enforce the fields match our expectations
//		$pdoImage = Image::getImageByImageTweetId($this->getPDO(), $this->tweet->getTweetId());
//		$this->assertEquals($numRows + 1, $this->getConnection()->getRowCount("image"));
//
//		// enforce no other objects are bleeding into the test
//		$this->assertContainsOnlyInstancesOf("Edu\\Cnm\\DataDesign\\Like", $pdoImage );
//	}
//
//	/**
//	 * test grabbing a Image by a tweet id that does not exist
//	 **/
//	public function testGetInvalidImageByTweetId() : void {
//		// grab a tweet id that exceeds the maximum allowable tweet id
//		$image = Image::getImageByImageTweetId($this->getPDO(), generateUuidV4());
//		$this->assertCount(0, $image);
//	}




}