<?php

$REGISTER_LTI2 = array(
"name" => "Sample Collegis Tool",
"FontAwesome" => "fa-server",
"short_name" => "Collegis Tool",
"description" => "This is a sample tool created by Collegis to test Tsugi with Bb Learn.",
    // By default, accept launch messages..
    "messages" => array("launch"),
    "privacy_level" => "name_only",  // anonymous, name_only, public
    "license" => "Apache",
    "languages" => array(
        "English", "Spanish"
    ),
    "source_url" => "http://cegitrepo-ob-1p/BbLearn_TSUGI/sample-project",
    // For now Tsugi tools delegate this to /lti/store
    "placements" => array(
        /*
        "course_navigation", "homework_submission",
        "course_home_submission", "editor_button",
        "link_selection", "migration_selection", "resource_selection",
        "tool_configuration", "user_navigation"
        */
    )

);
