<?php # index.php starting place for the website
include 'SXSuite/SXSuite.php';
use SXSuite\Template;
$generalHeading = new SXSuite\Template("templates/generalHeading.html");
define('pageTitle', 'CWR Trucking');
$generalHeading->set("title", pageTitle);

$template = new SXSuite\Template("templates/contactTemplate.html");
$template->set("head", $generalHeading->render());
$template->set("header", SXSuite\Template::render_immediate("templates/headerTemplate.html"));
$template->set("contactIntroBanner", SXSuite\Template::render_immediate("templates/contactIntroBannerTemplate.html"));
$template->set("contactForm", SXSuite\Template::render_immediate("templates/contactFormTemplate.html"));
$template->set("footer", SXSuite\Template::render_immediate("templates/footerTemplate.html"));
echo $template->render(); # renders the $template to be viewed/echo.