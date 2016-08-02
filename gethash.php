<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
sleep(1);
print("<pre style=\"white-space: pre-wrap; word-wrap: break-word;\">");
$db = new SQLite3('/opt/Responder/Responder.db');
//$res = $db->querySingle("SELECT *, group_concat(fullhash, x'0a') as ghash," .
// "group_concat(cleartext, '; ') as gclear, COUNT(*) as hashcount FROM responder WHERE client='" .
// $_SERVER['REMOTE_ADDR'] .
$res = $db->querySingle("SELECT *, group_concat(fullhash, x'0a') as ghash," .
 "group_concat(cleartext, '; ') as gclear, COUNT(*) as hashcount FROM responder WHERE client='" .
 $_SERVER['REMOTE_ADDR'] .
 "' AND timestamp > datetime('now', '-2 minute')", true);
if ($res['hashcount']) {
    print("NTLM hash is leaked on " . $res['timestamp'] . " (UTC)" . PHP_EOL);
    print("You're " . $res['user'] . PHP_EOL);

    if ($res['cleartext'] == '') {
        print("Trying to crack the hash, please wait for a while…" . PHP_EOL);
        //print("Hash bruteforcing is temporary disabled." . PHP_EOL);
    }
    else if ($res['cleartext'] == '!!NOTFOUND!!') {
        print("Plaintext password not found in our small dictionary." . PHP_EOL);
    }
    else {
        print("<b>Password found!</b> Your password is: " . $res['gclear'] . PHP_EOL);
        print("Unique hashes from your IP: " . $res['hashcount'] . PHP_EOL);
    }

    print(PHP_EOL);
    print($res['ghash']);

}
else {
    print("No NTLM hash is leaked. Try to manually copy&paste file://witch.valdikss.org.ru/a to the address bar." . PHP_EOL);
    print("(Works only on Windows with IE/Edge/Chrome)");
}
print("</pre>");
