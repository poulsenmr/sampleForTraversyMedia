<?php # index.php starting place for the website
include 'SXSuite/SXSuite.php';
use SXSuite\Template;
$generalHeading = new SXSuite\Template("templates/generalHeading.html");
define('pageTitle', 'CWR Trucking');
$generalHeading->set("title", pageTitle);

$template = new SXSuite\Template("templates/indexTemplate.html");
$template->set("head", $generalHeading->render());
$template->set("header", SXSuite\Template::render_immediate("templates/headerTemplate.html"));
$template->set("topBanner", SXSuite\Template::render_immediate("templates/topBannerTemplate.html"));
$template->set("middleContactBanner", SXSuite\Template::render_immediate("templates/middleContactBannerTemplate.html"));
$template->set("aboutUs", SXSuite\Template::render_immediate("templates/aboutUsTemplate.html"));
$template->set("footer", SXSuite\Template::render_immediate("templates/footerTemplate.html"));
echo $template->render(); # renders the $template to be viewed/echo.