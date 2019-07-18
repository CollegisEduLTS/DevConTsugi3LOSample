<?php
require_once "../config.php";

// The Tsugi PHP API Documentation is available at:
// http://do1.dr-chuck.com/tsugi/phpdoc/

use \Tsugi\Core\LTIX;
use \Tsugi\Util\Net;

$wwwroot = 'http://localhost/tsugi';

// No parameter means we require CONTEXT, USER, and LINK
$LAUNCH = LTIX::requireData();

// This is the Bb REST API stuff
require_once 'classes/Rest.class.php';
require_once 'classes/Token.class.php';
require_once 'classes/Datasource.class.php';
require_once 'classes/Term.class.php';
require_once 'classes/Course.class.php';
require_once 'classes/User.class.php';
require_once 'classes/Membership.class.php';

// Get the hostname from the LTI parameters
$ltiParams = $LAUNCH->session_get('lti_post');
$launchUrl = $ltiParams['launch_presentation_return_url'];
$baseUrl = parse_url($launchUrl, PHP_URL_SCHEME) . "://" . parse_url($launchUrl, PHP_URL_HOST);

$rest = new Rest($baseUrl);
$token = new Token();
$datasource = new Datasource();
$term = new Term();
$membership = new Membership();

// Mapping the IMS Roles
$strRoles = $ltiParams['roles'];
switch ($strRoles) {
    case 'urn:lti:sysrole:ims/lis/SysAdmin':{ $role = 'SysAdmin';
            break;}
    case 'urn:lti:sysrole:ims/lis/SysSupport':{ $role = 'SysSupport';
            break;}
    case 'urn:lti:sysrole:ims/lis/Creator':{ $role = 'Creator';
            break;}
    case 'urn:lti:sysrole:ims/lis/AccountAdmin':{ $role = 'AccountAdmin';
            break;}
    case 'urn:lti:sysrole:ims/lis/User':{ $role = 'User';
            break;}
    case 'urn:lti:sysrole:ims/lis/Administrator':{ $role = 'Administrator';
            break;}
    case 'urn:lti:sysrole:ims/lis/None':{ $role = 'None';
            break;}
    case 'urn:lti:instrole:ims/lis/Student':{ $role = 'Student';
            break;}
    case 'urn:lti:instrole:ims/lis/Faculty':{ $role = 'Faculty';
            break;}
    case 'urn:lti:instrole:ims/lis/Member':{ $role = 'Member';
            break;}
    case 'urn:lti:instrole:ims/lis/Learner':{ $role = 'Learner';
            break;}
    case 'urn:lti:instrole:ims/lis/Instructor':{ $role = 'Instructor';
            break;}
    case 'urn:lti:instrole:ims/lis/Mentor':{ $role = 'Mentor';
            break;}
    case 'urn:lti:instrole:ims/lis/Staff':{ $role = 'Staff';
            break;}
    case 'urn:lti:instrole:ims/lis/Alumni':{ $role = 'Alumni';
            break;}
    case 'urn:lti:instrole:ims/lis/ProspectiveStudent':{ $role = 'ProspectiveStudent';
            break;}
    case 'urn:lti:instrole:ims/lis/Guest':{ $role = 'Guest';
            break;}
    case 'urn:lti:instrole:ims/lis/Other':{ $role = 'Other';
            break;}
    case 'urn:lti:instrole:ims/lis/Administrator':{ $role = 'Administrator';
            break;}
    case 'urn:lti:instrole:ims/lis/Observer':{ $role = 'Observer';
            break;}
    case 'urn:lti:instrole:ims/lis/None':{ $role = 'None';
            break;}
    case 'urn:lti:role:ims/lis/Learner':{ $role = 'Learner';
            break;}
    case 'urn:lti:role:ims/lis/Learner/Learner':{ $role = 'Learner/Learner';
            break;}
    case 'urn:lti:role:ims/lis/Learner/NonCreditLearner':{ $role = 'Learner/NonCreditLearner';
            break;}
    case 'urn:lti:role:ims/lis/Learner/GuestLearner':{ $role = 'Learner/GuestLearner';
            break;}
    case 'urn:lti:role:ims/lis/Learner/ExternalLearner':{ $role = 'Learner/ExternalLearner';
            break;}
    case 'urn:lti:role:ims/lis/Learner/Instructor':{ $role = 'Learner/Instructor';
            break;}
    case 'urn:lti:role:ims/lis/Instructor':{ $role = 'Instructor';
            break;}
    case 'urn:lti:role:ims/lis/Instructor/PrimaryInstructor':{ $role = 'Instructor/PrimaryInstructor';
            break;}
    case 'urn:lti:role:ims/lis/Instructor/Lecturer':{ $role = 'Instructor/Lecturer';
            break;}
    case 'urn:lti:role:ims/lis/Instructor/GuestInstructor':{ $role = 'Instructor/GuestInstructor';
            break;}
    case 'urn:lti:role:ims/lis/Instructor/ExternalInstructor':{ $role = 'Instructor/ExternalInstructor';
            break;}
    case 'urn:lti:role:ims/lis/ContentDeveloper':{ $role = 'ContentDeveloper';
            break;}
    case 'urn:lti:role:ims/lis/ContentDeveloper/ContentDeveloper':{ $role = 'ContentDeveloper/ContentDeveloper';
            break;}
    case 'urn:lti:role:ims/lis/ContentDeveloper/Librarian':{ $role = 'ContentDeveloper/Librarian';
            break;}
    case 'urn:lti:role:ims/lis/ContentDeveloper/ContentExpert':{ $role = 'ContentDeveloper/ContentExpert';
            break;}
    case 'urn:lti:role:ims/lis/ContentDeveloper/ExternalContentExpert':{ $role = 'ContentDeveloper/ExternalContentExpert';
            break;}
    case 'urn:lti:role:ims/lis/Member':{ $role = 'Member';
            break;}
    case 'urn:lti:role:ims/lis/Member/Member':{ $role = 'Member/Member';
            break;}
    case 'urn:lti:role:ims/lis/Manager':{ $role = 'Manager';
            break;}
    case 'urn:lti:role:ims/lis/Manager/AreaManager':{ $role = 'Manager/AreaManager';
            break;}
    case 'urn:lti:role:ims/lis/Manager/CourseCoordinator':{ $role = 'Manager/CourseCoordinator';
            break;}
    case 'urn:lti:role:ims/lis/Manager/Observer':{ $role = 'Manager/Observer';
            break;}
    case 'urn:lti:role:ims/lis/Manager/ExternalObserver':{ $role = 'Manager/ExternalObserver';
            break;}
    case 'urn:lti:role:ims/lis/Mentor':{ $role = 'Mentor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/Mentor':{ $role = 'Mentor/Mentor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/Reviewer':{ $role = 'Mentor/Reviewer';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/Advisor':{ $role = 'Mentor/Advisor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/Auditor':{ $role = 'Mentor/Auditor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/Tutor':{ $role = 'Mentor/Tutor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/LearningFacilitator':{ $role = 'Mentor/LearningFacilitator';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalMentor':{ $role = 'Mentor/ExternalMentor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalReviewer':{ $role = 'Mentor/ExternalReviewer';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalAdvisor':{ $role = 'Mentor/ExternalAdvisor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalAuditor':{ $role = 'Mentor/ExternalAuditor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalTutor':{ $role = 'Mentor/ExternalTutor';
            break;}
    case 'urn:lti:role:ims/lis/Mentor/ExternalLearningFacilitator':{ $role = 'Mentor/ExternalLearningFacilitator';
            break;}
    case 'urn:lti:role:ims/lis/Administrator':{ $role = 'Administrator';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/Administrator':{ $role = 'Administrator/Administrator';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/Support':{ $role = 'Administrator/Support';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/ExternalDeveloper':{ $role = 'Administrator/Developer';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/SystemAdministrator':{ $role = 'Administrator/SystemAdministrator';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/ExternalSystemAdministrator':{ $role = 'Administrator/ExternalSystemAdministrator';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/ExternalDeveloper':{ $role = 'Administrator/ExternalDeveloper';
            break;}
    case 'urn:lti:role:ims/lis/Administrator/ExternalSupport':{ $role = 'Administrator/ExternalSupport';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant':{ $role = 'TeachingAssistant';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistant':{ $role = 'TeachingAssistant/TeachingAssistant';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistantSection':{ $role = 'TeachingAssistant/TeachingAssistantSection';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistantSectionAssociation':{ $role = 'TeachingAssistant/';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistantOffering':{ $role = 'TeachingAssistant/';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistantTemplate':{ $role = 'TeachingAssistant/';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/TeachingAssistantGroup':{ $role = 'TeachingAssistant/TeachingAssistantGroup';
            break;}
    case 'urn:lti:role:ims/lis/TeachingAssistant/Grader':{ $role = 'TeachingAssistant/Grader';
            break;}

}

// Learn authentication
if (isset($_SESSION['bb_rest_token'])) {
    $access_token = $_SESSION['bb_rest_token'];
} else {
    if (isset($_GET['code'])) {
        $token = $rest->authorize($_GET['code'], $wwwroot . '/mod/collegis_test/' . addSession('index.php'));
        $access_token = $token->access_token;
        $_SESSION['bb_rest_token'] = $access_token;

        $userUuid = $token->user_id;
        $user = $rest->readUser($access_token, $userUuid);
        $_SESSION['success'] = 'Successfully authenticated as ' . $user->userName . ' with the role ' . $role;

    } else {
        $rest->authorizeUser($wwwroot . '/mod/collegis_test/' . addSession('index.php'));
        return;
    }
}

$course = null;

if (isset($_POST['code']) && isset($_POST['set'])) {
    $course = $rest->readCourse($access_token, $_POST['code']);
    $_SESSION['success'] = 'Search complete for ' . $_POST['code'];
} elseif (isset($_POST['getAll'])) {
    $course = $rest->readCourse($access_token, null);
    $_SESSION['success'] = "There are " . count($course) . " courses.";
}

// Render view
$OUTPUT->header();
// Set bodyStart to false to allow it to render even on a POST
$OUTPUT->bodyStart(false);
$OUTPUT->topNav();
$OUTPUT->welcomeUserCourse();
$OUTPUT->flashMessages();

echo ('<form method="post">');
echo (__("Enter course_id:") . "\n");
echo ('<input type="text" name="code"> ');
echo ('<input type="submit" class="btn btn-normal" name="set" value="Search"> ');
echo ('<input type="submit" class="btn btn-normal" name="getAll" value="Load All Courses"> ');
echo ("\n</form>\n");

if (isset($course)) {
    echo ('<table border="1">' . "\n");
    echo ("<tr><th>Course ID</th><th>Created</th>");
    if (is_array($course)) {
        foreach ($course as $c) {
            echo "<tr><td>";
            echo ($c->courseId);
            echo ("</td><td>");
            echo ($c->created);
            echo ("</td></tr>\n");
        }
    } else {
        echo "<tr><td>";
        echo ($course->courseId);
        echo ("</td><td>");
        echo ($course->created);
        echo ("</td></tr>\n");
    }
    echo ("</table>\n");
}

$OUTPUT->footer();
