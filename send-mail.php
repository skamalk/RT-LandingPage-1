<?php
// Configuration
$to = "sales@Rocketeams.com";
$from = "noreply@Rocketeams.com";
$subject = "New Landing Page Inquiry";

function clean_input($value)
{
    $value = trim((string) $value);
    $value = str_replace(array("\r", "\n"), array(" ", " "), $value);
    return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
}

function reject_header_injection($value)
{
    return preg_match('/[\r\n]/', (string) $value) === 1;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method not allowed.";
    exit;
}

$raw_name = $_POST["name"] ?? "";
$raw_email = $_POST["email"] ?? "";
$raw_phone = $_POST["phone"] ?? "";
$raw_company = $_POST["company"] ?? "";
$raw_budget = $_POST["budget"] ?? "";
$raw_timeline = $_POST["timeline"] ?? "";
$raw_message = $_POST["message"] ?? "";
$raw_form_source = $_POST["form_source"] ?? "Landing page form";

$name = clean_input($raw_name);
$email = clean_input($raw_email);
$phone = clean_input($raw_phone);
$company = clean_input($raw_company);
$budget = clean_input($raw_budget);
$timeline = clean_input($raw_timeline);
$message = clean_input($raw_message);
$form_source = clean_input($raw_form_source);

$errors = array();

if ($name === "") {
    $errors[] = "Name is required.";
}

if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
}

if ($phone === "" || !preg_match('/^[+()\-\s0-9]{7,20}$/', $phone)) {
    $errors[] = "A valid phone number is required.";
}

foreach (array($raw_name, $raw_email, $raw_phone, $raw_company, $raw_budget, $raw_timeline, $raw_form_source) as $header_value) {
    if (reject_header_injection($header_value)) {
        $errors[] = "Invalid form data.";
        break;
    }
}

if (!empty($errors)) {
    http_response_code(422);
    echo implode(" ", $errors);
    exit;
}

$submission_date = date("Y-m-d H:i:s");
$ip_address = $_SERVER["REMOTE_ADDR"] ?? "Unavailable";
$user_agent = $_SERVER["HTTP_USER_AGENT"] ?? "Unavailable";

$email_body = "
New Landing Page Inquiry

Name: {$name}
Email: {$email}
Phone: {$phone}
Company / Nature of Business: {$company}
Budget: {$budget}
Timeline: {$timeline}
Form Source: {$form_source}

Message:
{$message}

Submission Date: {$submission_date}
IP Address: {$ip_address}
User Agent: {$user_agent}
";

$headers = array(
    "From: Rocketeams <{$from}>",
    "Reply-To: {$name} <{$email}>",
    "MIME-Version: 1.0",
    "Content-Type: text/plain; charset=UTF-8",
    "X-Mailer: PHP/" . phpversion()
);

if (mail($to, $subject, $email_body, implode("\r\n", $headers))) {
    header("Location: thank-you.html");
    exit;
}

http_response_code(500);
echo "Sorry, your message could not be sent. Please try again later.";
