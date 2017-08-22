<?php
#Our textbox to be full width and at least 500px tall
echo <<< STYLE
<style>
#textbox {
    width: 100%;
    min-height: 500px;
}
</style>
STYLE;
require "scripts/pi-hole/php/header.php";
#Check to make sure that the custom DNSMasq file exists and custom hosts file exists, if not call sudo pihole addhosts to create it
#pihole addhosts will prefix the path of /etc/dnsmasq.d/ and will take the basename of whatever we provide here anyways to avoid ../../../etc/hosts foolishness
if (!file_exists("/etc/dnsmasq.d/02-custom-hosts.conf") || !file_exists("/etc/hosts.local")) {
	echo "/etc/dnsmasq.d/02-custom-hosts.conf doesn't exist, setting up for first time use";
	exec("sudo pihole addhosts 02-custom-hosts.conf /etc/hosts.local");
}
$hosts_file = "/etc/hosts.local";
$token = $_SESSION['token'];
if (isset($_POST["content"])) {
	$content = $_POST["content"];
	#Add a newline at the end of the content
	$content = "$content\n";
	file_put_contents($hosts_file,$content);
	$file_contents = file_get_contents($hosts_file);
	exec("sudo pihole -a restartdns");
	#Restarted DNSmasq and now display (readonly) the file in another text box
	echo "<textarea readonly id=\"textbox\" name=\"content\" id=\"textbox\">$file_contents</textarea>";
	echo "<center><a href=\"static.php\"><button>Go Back to Edit Static Host File</button></a></center>";
	exit(0);
}

if (!file_exists($hosts_file)) {
	#Make sure $hosts_file exists
	echo "$hosts_file does not exist";
	require "scripts/pi-hole/php/footer.php";
	exit(1);
} else {
	#Display the name of the file we are modifying for clarity
	echo "Modifying <i>$hosts_file</i>";
}

$file_contents = file_get_contents($hosts_file);
#Display the form with the current contents of the file, this textbox is editable and will post and restart when they submit
echo <<< FORM
	<form action="" method="POST">
	<textarea id="textbox" name="content" id="textbox">$file_contents</textarea>
	<br><center>
	<input type="submit" name="save" value="Submit and Restart" />
	<input type="reset" name="reset" value="Reset" />
	</center>
	</form>
FORM;

#Close with the footer
require "scripts/pi-hole/php/footer.php";
?>
