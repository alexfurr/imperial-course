<?php 
/* Template Name: Topics Home Page */

get_header(); 
?>
<?php
// This is the template for the topics home
 get_header(); ?>
<main id="content">

<br><br>
<?php
$topicStr = imperialCourse_draw::drawTopics();
echo $topicStr;
?>


    <br class="clearB">

</main>
<?php get_sidebar(); ?>
<?php get_footer(); ?>