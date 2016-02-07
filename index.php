<?php
echo '<html><head><meta charset="utf-8" /><title>ＷＩＴＣＨ？</title></head>
<body style="background-image: url(witch.png); background-repeat: no-repeat; background-attachment: fixed; background-position: right top;">
<p style="text-align:center;">ＷＩＴＣＨ？</p>
<pre>';
$pof_output = array();
exec("/home/p0f-mtu/tools/p0f-client /home/p0f-logs/socket ".$_SERVER['REMOTE_ADDR'], $pof_output);
$pof_output = implode("\n", $pof_output);
echo $pof_output;

//$ptr = dns_get_record($_SERVER['REMOTE_ADDR'], DNS_PTR);
$home_user = False;
$ip = $_SERVER['REMOTE_ADDR'];
//$ip = '4.244.78.41';
$parts = explode('.',$ip);
$reverse_ip = implode('.', array_reverse($parts)).".in-addr.arpa.";

$ptr = dns_get_record($reverse_ip, DNS_PTR);
if (!empty($ptr)) {
    $ptr = $ptr[0]['target'];

    $regexp_array = array('~.*ppp-(.*)~',
    '~dsl-pool~',
    '~.*\.(pool|pppoe|adsl|dsl|xdsl|dialup|broad|cust-adsl|dynamicip|dynamicIP|dyn)\..*~',
    '~(pool|pppoe|adsl|dsl|xdsl|dialup|broad|cust-adsl|dynamicip|dynamicIP|dyn)\..*~',
    '~(pool|pppoe|adsl|dsl|xdsl|dialup|broad|cust-adsl|dynamicip|dynamicIP|dyn)-.*~',
    '~ip\-[a-fA-F0-9]+\-.*~',
    '~.*([0-9]+)(\.|-)([0-9]+)(\.|-)([0-9]+).*~',
    '~([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+)\..*~',
    '~([0-9]+)\.([0-9]+)\.([0-9]+)\.([0-9]+)\..*~');

    foreach ($regexp_array as $regexp) {
        if (preg_match($regexp, $ptr) === 1) {
            # Found
            $home_user = True;
            break;
        }
    }


    echo "\nPTR           = ";
    echo $ptr;
} else { 
    $home_user = True;
}
echo "\n\n";
echo "PTR test      = ";
if ($home_user) {echo 'Probably home user';} else {echo 'Probably server user';}
echo "\n";

if (strpos($pof_output, "ID OS mismatch") !== False)
    echo "Fingerprint and User-Agent mismatch. Either proxy or User-Agent spoofing.";
else if (strpos($pof_output, "ID is fake") !== False)
    echo "Your fingerprint is fake. This is probably a false positive.";
else echo "Fingerprint and OS match. No proxy detected (this test does not include headers detection).";
echo "\n";

preg_match('/MTU.+= ([0-9]{1,5})/', $pof_output, $mtu);
if (!empty($mtu)) $mtu = $mtu[1];

if (strpos($pof_output, "OpenVPN") !== False) {
    if ($mtu < 1360)
        echo "Probable OpenVPN detected. If it really is OpenVPN, then it's settings are as follows:\n";
    else
        echo "OpenVPN detected. ";

    if (strpos($pof_output, "bs64") !== False)
        echo "Block size is 64 bytes long (probably Blowfish), ";
    if (strpos($pof_output, "bs128") !== False)
        echo "Block size is 128 bytes long (probably AES), ";
    if (strpos($pof_output, "SHA1") !== False)
        echo "MAC is SHA1";
    if (strpos($pof_output, "SHA256") !== False)
        echo "MAC is SHA256";
    if (strpos($pof_output, "lzo") !== False)
        echo ", LZO compression enabled.\n";
    else
        echo ".\n";
} else if ($mtu % 2 == 1)
        echo "MTU is strange. Probably OpenVPN.";
    else
        echo "No OpenVPN detected.";


echo '</pre>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter31611883 = new Ya.Metrika({
                    id:31611883,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    ut:"noindex"
                });
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
            s = d.createElement("script"),
            f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = "https://mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="https://mc.yandex.ru/watch/31611883?ut=noindex" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter --></body></html>';
