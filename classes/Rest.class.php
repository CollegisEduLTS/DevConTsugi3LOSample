<?php
require_once 'HTTP/Request2.php';
require_once 'classes/Availability.class.php';
require_once 'classes/Constants.class.php';
require_once 'classes/Contact.class.php';
require_once 'classes/Name.class.php';

class Rest
{

    public $constants = '';
    public $hostname = null;
    public $results = [];

    public function __construct($hostname)
    {
        $this->hostname = $hostname;
    }

    public function authorize($code, $redirectUri)
    {

        $constants = new Constants();
        $token = new Token();

        $requestUrl = $this->hostname . $constants->AUTH_PATH . "?code=$code&redirect_uri=$redirectUri";
        $request = new HTTP_Request2($requestUrl, HTTP_Request2::METHOD_POST);
        $request->setAuth($constants->KEY, $constants->SECRET, HTTP_Request2::AUTH_BASIC);
        if (is_null($code)) {
            $request->setBody('grant_type=client_credentials');
        } else {
            $request->setBody('grant_type=authorization_code');
        }
        $request->setHeader('Content-Type', 'application/x-www-form-urlencoded');

        // Ignoring certificate errors
        if ($constants->IGNORE_CERTS) {
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));
        }

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $token = json_decode($response->getBody());
            } else {
                $_SESSION['error'] = 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }

        return $token;
    }

    public function authorizeUser($redirectUri)
    {
        $constants = new Constants();
        $requestUrl = $this->hostname . $constants->AUTH_PATH_3LO . "?response_type=code&redirect_uri=$redirectUri&client_id=" . $constants->KEY . "&scope=read write delete";
        header('Location: ' . $requestUrl);
        return;
    }

    public function getNextPage($access_token, $url)
    {
        $request = new HTTP_Request2($this->hostname . $url, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $course = json_decode($response->getBody());

                $this->results = array_merge($this->results, $course->results);
                if (property_exists($course, 'paging')) {
                    // echo("-There is another page of results...getting those, too");
                    $this->getNextPage($access_token, $course->paging->nextPage);
                } 
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }
    }

    public function createDatasource($access_token)
    {
        $constants = new Constants();
        $datasource = new Datasource();

        $request = new HTTP_Request2($this->hostname . $constants->DSK_PATH, HTTP_Request2::METHOD_POST);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($datasource));

        // Ignoring certificate errors
        if ($constants->IGNORE_CERTS) {
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));
        }

        try {
            $response = $request->send();
            if (201 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Create Datasource...</br>";
                $datasource = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $datasource;
    }

    public function readDatasource($access_token, $dsk_id)
    {
        $constants = new Constants();
        $datasource = new Datasource();

        $request = new HTTP_Request2($this->hostname . $constants->DSK_PATH . '/' . $dsk_id, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        // Ignoring certificate errors
        if ($constants->IGNORE_CERTS) {
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));
        }

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Read Datasource...</br>";
                $datasource = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $datasource;
    }

    public function updateDatasource($access_token, $dsk_id)
    {
        $constants = new Constants();
        $datasource = new Datasource();

        $datasource->id = $dsk_id;

        $request = new HTTP_Request2($this->hostname . $constants->DSK_PATH . '/' . $dsk_id, 'PATCH');
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($datasource));

        // Ignoring certificate errors
        if ($constants->IGNORE_CERTS) {
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));
        }

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Update Datasource...</br>";
                $datasource = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $datasource;
    }

    public function deleteDatasource($access_token, $dsk_id)
    {
        $constants = new Constants();

        $request = new HTTP_Request2($this->hostname . $constants->DSK_PATH . '/' . $dsk_id, HTTP_Request2::METHOD_DELETE);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');

        // Ignoring certificate errors
        if ($constants->IGNORE_CERTS) {
            $request->setConfig(array(
                'ssl_verify_peer' => false,
                'ssl_verify_host' => false,
            ));
        }

        try {
            $response = $request->send();
            if (204 == $response->getStatus()) {
                $_SESSION['error'] =  "Datasource Deleted";
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }

    public function createTerm($access_token, $dsk_id)
    {
        $constants = new Constants();
        $term = new Term();

        $term->dataSourceId = $dsk_id;
        $term->availability = new Availability();

        $request = new HTTP_Request2($this->hostname . $constants->TERM_PATH, HTTP_Request2::METHOD_POST);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($term));

        try {
            $response = $request->send();
            if (201 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Create Term...</br>";
                $term = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $term;
    }

    public function readTerm($access_token, $term_id)
    {
        $constants = new Constants();
        $term = new Term();

        $request = new HTTP_Request2($this->hostname . $constants->TERM_PATH . '/' . $term_id, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Read Term...</br>";
                $datasource = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $term;
    }

    public function updateTerm($access_token, $dsk_id, $term_id)
    {
        $constants = new Constants();
        $term = new Term();

        $term->id = $term_id;
        $term->dataSourceId = $dsk_id;

        $request = new HTTP_Request2($this->hostname . $constants->TERM_PATH . '/' . $term_id, 'PATCH');
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($term));

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Update Term...</br>";
                $datasource = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $term;
    }

    public function deleteTerm($access_token, $term_id)
    {
        $constants = new Constants();

        $request = new HTTP_Request2($this->hostname . $constants->TERM_PATH . '/' . $term_id, HTTP_Request2::METHOD_DELETE);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');

        try {
            $response = $request->send();
            if (204 == $response->getStatus()) {
                $_SESSION['error'] =  "Term Deleted";
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }

    public function createCourse($access_token, $dsk_id, $term_id)
    {
        $constants = new Constants();
        $course = new Course();

        $course->dataSourceId = $dsk_id;
        $course->termId = $term_id;
        $course->availability = new Availability();

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH, HTTP_Request2::METHOD_POST);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($course));

        try {
            $response = $request->send();
            if (201 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Create Course...</br>";
                $course = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $course;
    }

    public function readCourse($access_token, $course_id)
    {
        $constants = new Constants();
        $course = new Course();

        if (is_null($course_id)) {
            // echo('Loading all of the courses');
            $requestUrl = $this->hostname . $constants->COURSE_PATH;
        } else {
            // echo('Loading only the entered course - ' . $course_id);
            $requestUrl = $this->hostname . $constants->COURSE_PATH . '/courseId:' . $course_id;
        }

        $request = new HTTP_Request2($requestUrl, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $course = json_decode($response->getBody());
                if (property_exists($course,'results') && is_array($course->results)) {
                    // echo("This  is an array...putting it into the results array");
                    $this->results = array_merge($this->results, $course->results);
                    if (property_exists($course, 'paging')) {
                        // echo("-There is another page of results...getting them");
                        $this->getNextPage($access_token, $course->paging->nextPage);
                    }
                } else {
                    // echo('Only one course was found.');
                }
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        if (is_null($course_id)){
            return $this->results;
        } else {
            return $course;
        }
    }

    public function updateCourse($access_token, $dsk_id, $course_id, $course_uuid, $course_created, $termId)
    {
        $constants = new Constants();
        $course = new Course();

        $course->id = $course_id;
        $course->uuid = $course_uuid;
        $course->created = $course_created;
        $course->dataSourceId = $dsk_id;
        $course->termId = $termId;

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id, 'PATCH');
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($course));

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Update Course...</br>";
                $course = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $course;
    }

    public function deleteCourse($access_token, $course_id)
    {
        $constants = new Constants();

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id, HTTP_Request2::METHOD_DELETE);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');

        try {
            $response = $request->send();
            if (204 == $response->getStatus()) {
                $_SESSION['error'] =  "Course Deleted";
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }

    public function createUser($access_token, $dsk_id)
    {
        $constants = new Constants();
        $user = new User();

        $user->dataSourceId = $dsk_id;
        $user->availability = new Availability();
        $user->name = new Name();
        $user->contact = new Contact();

        $request = new HTTP_Request2($this->hostname . $constants->USER_PATH, HTTP_Request2::METHOD_POST);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($user));

        try {
            $response = $request->send();
            if (201 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Create User...</br>";
                $user = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $user;
    }

    public function readUser($access_token, $user_id)
    {
        $constants = new Constants();
        $user = new User();

        $requestUrl = $this->hostname . $constants->USER_PATH . '/uuid:' . $user_id;
        $request = new HTTP_Request2($requestUrl, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $user = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $user;
    }

    public function updateUser($access_token, $dsk_id, $user_id, $user_uuid, $user_created)
    {
        $constants = new Constants();
        $user = new User();

        $user->id = $user_id;
        $user->uuid = $user_uuid;
        $user->created = $user_created;
        $user->dataSourceId = $dsk_id;

        $request = new HTTP_Request2($this->hostname . $constants->USER_PATH . '/' . $user_id, 'PATCH');
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($user));

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Update User...</br>";
                $user = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $user;
    }

    public function deleteUser($access_token, $user_id)
    {
        $constants = new Constants();

        $request = new HTTP_Request2($this->hostname . $constants->USER_PATH . '/' . $user_id, HTTP_Request2::METHOD_DELETE);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');

        try {
            $response = $request->send();
            if (204 == $response->getStatus()) {
                $_SESSION['error'] =  "User Deleted";
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }

    public function createMembership($access_token, $dsk_id, $course_id, $user_id)
    {
        $constants = new Constants();
        $membership = new Membership();

        $membership->dataSourceId = $dsk_id;
        $membership->availability = new Availability();
        $membership->userId = $user_id;
        $membership->courseId = $course_id;

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, HTTP_Request2::METHOD_PUT);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($membership));

        try {
            $response = $request->send();
            if (201 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Create Membership...</br>";
                $membership = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $membership;
    }

    public function readMembership($access_token, $course_id, $user_id)
    {
        $constants = new Constants();
        $membership = new Membership();

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, HTTP_Request2::METHOD_GET);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Read Membership...</br>";
                $membership = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $membership;
    }

    public function updateMembership($access_token, $dsk_id, $course_id, $user_id, $membership_created)
    {
        $constants = new Constants();
        $membership = new Membership();

        $membership->dataSourceId = $dsk_id;
        $membership->userId = $user_id;
        $membership->courseId = $course_id;
        $membership->created = $membership_created;

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, 'PATCH');
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');
        $request->setBody(json_encode($membership));

        try {
            $response = $request->send();
            if (200 == $response->getStatus()) {
                $_SESSION['error'] =  "</br> Update Membership...</br>";
                $membership = json_decode($response->getBody());
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
        }

        return $membership;
    }

    public function deleteMembership($access_token, $course_id, $user_id)
    {
        $constants = new Constants();

        $request = new HTTP_Request2($this->hostname . $constants->COURSE_PATH . '/' . $course_id . '/users/' . $user_id, HTTP_Request2::METHOD_DELETE);
        $request->setHeader('Authorization', 'Bearer ' . $access_token);
        $request->setHeader('Content-Type', 'application/json');

        try {
            $response = $request->send();
            if (204 == $response->getStatus()) {
                $_SESSION['error'] =  "Membership Deleted";
            } else {
                $_SESSION['error'] =  'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                $response->getReasonPhrase();
                $BbRestException = json_decode($response->getBody());
                var_dump($BbRestException);
                return false;
            }
        } catch (HTTP_Request2_Exception $e) {
            $_SESSION['error'] =  'Error: ' . $e->getMessage();
            return false;
        }

        return true;
    }
}
