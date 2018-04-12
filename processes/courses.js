// File:    courses.js
// Author:  Hank Feild
// Date:    02-Nov-2017
// Purpose: Contains code for transmitting data from courses.html to
//          process.php.

var studentId = "", semester = "";

/**
 * Sends a request to process.php with the given data as POST. The result
 * replaces the content of the given outputBoxElm.
 *
 * @param data The data to send to the server; should be an object with at
 *             lease an 'action' key.
 * @param outputBoxElm The jQuery element in which the result should be put.
 */
var getServerResponse = function(data, outputBoxElm){
    // Get the response.
    $.ajax('process.php', {
        data: data,
        method: 'post',
        success: function(data){
            outputBoxElm.html(data);
        },
        error: function(){
            alert('There was an error when submitting the form data.');
        }
    });
};

/**
 * Fetches the COS for the current semester and displays it in the COS output
 * box.
 *
 * @param semester The semester to display the COS for.
 */
var getCOS = function(semester){
    // Get the course of study.
    getServerResponse(
        {action: 'getCOS', semester: semester},
        $('#course-of-study-output'));

};

/**
 * Reads the course information from the form and submits it to the server.
 * The result is printed in the output box below the form.
 */
var onSelectStudent = function(event){
    var form = $('#student-schedule-form')
    semester = form.find('#semester').val();
    studentId = form.find('#studentId').val();

    // Get the COS.
    getCOS(semester);

    // Get the student schedule.
    getServerResponse({
        action: 'getStudentSchedule',
        semester: semester,
        studentId: studentId
    }, $('#student-schedule-output'));
};

/**
 * Reads the course information from the form and submits it to the server.
 * The result is printed in the output box below the form.
 */
var onAddClass = function(event){
    var form = $('#add-drop-class-form')
    var courseNo = form.find('#courseNo').val();
    var prefix = form.find('#prefix').val();
    var section = form.find('#section').val();

    // Get the student schedule.
    getServerResponse({
        action: 'addClass',
        semester: semester,
        studentId: studentId,
        courseNo: courseNo,
        prefix: prefix,
        section: section
    }, $('#add-drop-class-output'));
};

/**
 * Reads the course information from the form and submits it to the server.
 * The result is printed in the output box below the form.
 */
var onDropClass = function(event){
    var form = $('#add-drop-class-form')
    var courseNo = form.find('#courseNo').val();
    var prefix = form.find('#prefix').val();
    var section = form.find('#section').val();

    // Get the student schedule.
    getServerResponse({
        action: 'dropClass',
        semester: semester,
        studentId: studentId,
        courseNo: courseNo,
        prefix: prefix,
        section: section
    }, $('#add-drop-class-output'));
};


/**
 * Once the document is loaded, place listeners on the buttons.
 */
$(document).ready(function(){
    $(document).on('click', '#set-student', onSelectStudent);
    $(document).on('click', '#add-class', onAddClass);
    $(document).on('click', '#drop-class', onDropClass);
});
