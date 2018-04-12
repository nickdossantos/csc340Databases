<?php
// File:    process.php
// Author:  Nick Dos Santos
// Date:    02-Nov-2017
// Purpose: Processes requests related to courses and schedules. 

// For debugging:
error_reporting(E_ALL);
ini_set('display_errors', '1');

// TODO Add database info here. This should connect to your hw4 database.
$dbms       = 'mysql';
$host       = 'localhost';
$database   = 'ndossantos_hw4';
$dsn        = "$dbms:dbname=$database;host=$host";
$user       = 'ndossantos';
$password   = ;

// See if there is an 'action' request.
if(!isset($_POST['action'])){
    die("No action given :(");
}

// Open connection to database.
// TODO add database connection code. The code below assume you set
// $dbh.
//$dbh = null;
try {
    $dbh = new PDO($dsn, $user, $password);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

// Process the action; call the corresponding function to process it.
$action = $_POST['action'];

// Add a class to a student's schedule.
if($action == "addClass"){
    if(!hasPostParams(['studentId', 'semester', 'prefix', 'courseNo', 'section']))
        die('Missing required parameters!');
    addClass($dbh, $_POST['studentId'], $_POST['semester'], $_POST['prefix'],
        $_POST['courseNo'], $_POST['section']);

// Remove a class from a student's schedule.
} else if($action == "dropClass") {
    if(!hasPostParams(['studentId', 'semester', 'prefix', 'courseNo', 'section']))
        die('Missing required parameters!');
    dropClass($dbh, $_POST['studentId'], $_POST['semester'], $_POST['prefix'],
        $_POST['courseNo'], $_POST['section']);

// Get a student's schedule for the given semester.
} else if($action == "getStudentSchedule"){
    if(!hasPostParams(['studentId', 'semester']))
        die('Missing required parameters!');
    getStudentSchedule($dbh, $_POST['studentId'], $_POST['semester']);

// Get the course of study for the given semester.
} else if($action == "getCOS"){
    if(!hasPostParams(['semester']))
        die('Missing required parameters!');
    getCourseOfStudy($dbh, $_POST['semester']);
} else {
    die("Invalid action specified: {$_POST['action']}");
}

    
/**
 * Generates the HTML for a student's schedule in the given semester. The 
 * output should include for each course a student is signed up for:
 *   - semester
 *   - prefix
 *   - course no.
 *   - section
 *   - title
 *   - instructor
 *   - room
 *   - time
 *
 * This data should be output as an HTML table, with one class per row.
 * If no student with the given id is present, then the output should be an
 * error message saying as much.
 * 
 * @param dbh The PDO database handle.
 * @param studentId The id of the student.
 * @param semester The semester.
 */
function getStudentSchedule($dbh, $studentId, $semester){
    // TODO Implement this.
    $statement = $dbh->prepare('Select stu_id, courseOfStudy.course_num, courseOfStudy.inst_id, courseOfStudy.time, courseOfStudy.room_num, courseOfStudy.section_num, courses.title  From studentSchedule Inner Join courseOfStudy ON studentSchedule.cos_id=courseOfStudy.id Inner Join courses On courseOfStudy.course_num=courses.course_num where stu_id = :studentId and courseOfStudy.semester = :semester;');	
	$statement->execute(array(':studentId' => $studentId,':semester'=> $semester));
	//echo "<tr><td> Student ID</tr> </td>";
	$res = $statement->fetchAll();
	foreach($res as $row){
  		print "<tr><td> {$row["stu_id"]}</td> <td> {$row["course_num"]}</td> <td> {$row["inst_id"]}</td> <td> {$row["time"]}</td>  <td> {$row["room_num"]}</td> <td> {$row["section_num"]}</td> <td> {$row["title"]}</td></tr>";
	}
	echo " The student was not found in the database.";
}
  
/**
 * Adds the specified class to the given student's schedule for the given
 * semester. If no student with the given id is present, or no course with the
 * given prefix, courseNo, and section in the given semester exists, then the 
 * output should be an error message saying as much. If successful, a message
 * saying so should be output.
 * 
 * @param dbh The PDO database handle.
 * @param studentId The id of the student.
 * @param semester The semester of the course to add.
 * @param prefix The prefix of the course to add.
 * @param courseNo The course number of the course to add.
 * @param section The section of the course to add. 
 */
function addClass($dbh, $studentId, $semester, $prefix, $courseNo, $section){
    // TODO Implement this.
	$dbh->beginTransaction();
	try{
	$statement = $dbh->prepare('insert into studentSchedule(stu_id, cos_id) values (:studentId, (Select courseOfStudy.id from courseOfStudy where courseOfStudy.course_num = :courseNo and courseOfStudy.section_num = :section));');
	$statement->execute(array(':studentId'=> $studentId, ':courseNo'=> $courseNo, ':section' => $section));
	$dbh->commit();
	}catch(PDOException $e){
	$dbh->rollBack();
	print "There was an error with the request<br/>";
	}
    
}

/**
 * Drops the specified class from the given student's schedule for the given
 * semester. If no student with the given id is present, or no course with the
 * given prefix, courseNo, and section in the given semester exists in the
 * student's schedule, then the output should be an error message saying as 
 * much. If successful, a message saying so should be output.
 * 
 * @param dbh The PDO database handle.
 * @param studentId The id of the student.
 * @param semester The semester of the course to add.
 * @param prefix The prefix of the course to add.
 * @param courseNo The course number of the course to add.
 * @param section The section of the course to add. 
 */
function dropClass($dbh, $studentId, $semester, $prefix, $courseNo, $section){
	$dbh->beginTransaction();
	try{
		$statement = $dbh->prepare('DELETE FROM studentSchedule WHERE studentSchedule.cos_id IN (SELECT courseOfStudy.id FROM courseOfStudy WHERE courseOfStudy.course_num = :courseNo and courseOfStudy.section_num = :section);');
		$statement->execute(array(':courseNo' => $courseNo, ':section' => $section));
		$dbh->commit();
	}catch(PDOException $e){
	$dbh->rollback();
	print "There was an error with the request<br/>";
	}
}

/**
 * Outputs the course of student for the semester. For each course being
 * offered, the following information should be included:
 *   - semester
 *   - prefix
 *   - course no
 *   - section
 *   - title
 *   - instructor
 *   - room
 *   - time
 *   - capacity
 *   - current enrollement
 *
 * This data should be output as an HTML table, with one course per row.
 *
 * @param dbh The PDO database handle.
 * @param semester The semester of the COS to print.
 */
function getCourseOfStudy($dbh, $semester){
	$statement = $dbh->prepare('Select courseOfStudy.course_num, inst_id, time, room_num, max_stu, total_stu, section_num, courses.course_prefix, courses.title from courseOfStudy Inner Join courses On courseOfStudy.course_num = courses.course_num where semester = :semester;');
    	$statement->execute(array(':semester'=> $semester));
	$res = $statement->fetchAll();
        foreach($res as $row){
		print "<tr><td> {$row["course_num"]}</td> <td> {$row["inst_id"]}</td> <td> {$row["time"]}</td> <td> {$row["room_num"]}</td> <td> {$row["max_stu"]}</td> <td> {$row["total_stu"]}</td> <td> {$row["section_num"]}</td> <td> {$row["course_prefix"]}</td> <td> {$row["title"]}</td></tr>";
	}
}

/**
 * Checks if $_POST has each of the given parameters. If so, true is returned,
 * otherwise false.
 *
 * @param $params A list of parameters to check are set in $_POST.
 * @return Whether or not all of the params are set in $_POST.
 */
function hasPostParams($params){
    foreach($params as $param)
        if(!isset($_POST[$param]))
            return false;
    return true;
}
?>

