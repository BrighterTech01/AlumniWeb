<?php
    include_once '../components/connection.php';

    if(isset($_POST['update_course'])){
        $course_id = $_POST['course_id'];
        $course_name = $_POST['course_name'];
        $status = $_POST['status'];

        $query = "UPDATE course SET course_name = '$course_name', status = '$status' WHERE course_id = $course_id";
        $result = mysqli_query($conn,$query);

        if($result){
            header('location:../course.php');
        }
    }