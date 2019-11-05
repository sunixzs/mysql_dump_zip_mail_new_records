<?php

if (!defined('MODE') || MODE != 'INDEX') {
    die('Direct access denied!');
}

/**
 * @author sunixzs
 */
class Mailer
{
    /**
     * Sends a mail with a file attachement.
     *
     * @param string $to
     * @param string $subject
     * @param string $from
     * @param string $returnPath
     * @param string $replyTo
     * @param string $file
     */
    public function send($to, $subject, $from, $returnPath, $replyTo, $file)
    {
        $boundary = md5(uniqid(time()));
        $mailData = [
            'to' => $to,
            'subject' => $subject,

            // additional headers
            'header' => [
                'From' => $from,
                'Return-Path' => $returnPath,
                'Reply-To' => $replyTo,
                'X-Mailer' => 'PHP/' . phpversion(),
                'MIME-Version' => '1.0',
                'Content-Type' => 'multipart/mixed; boundary=PHP-mixed-' . $boundary,
            ],

            // file
            'file' => [
                'path' => $file,
                'name' => basename($file),
                'content' => file_get_contents($file),
            ],
        ];

        $rn = "\r\n";

        $content = 'This is a multi-part message in MIME format' . $rn;

        $content .= '--PHP-mixed-' . $boundary . $rn;
        $content .= 'Content-Type: multipart/alternative; boundary=PHP-alt-' . $boundary . $rn . $rn;

        $content .= '--PHP-alt-' . $boundary . $rn;
        $content .= 'Content-type: text/plain; charset="utf-8"' . $rn;
        $content .= 'Content-Transfer-Encoding: quoted-printable' . $rn . $rn;
        $content .= $rn . '' . $rn . $rn;
        $content .= '--PHP-alt-' . $boundary . '--' . $rn . $rn;

        $content .= '--PHP-mixed-' . $boundary . $rn;
        $content .= 'Content-Type: ' . mime_content_type($mailData['file']['path']) . '; name="' . $mailData['file']['name'] . '"' . $rn;
        $content .= 'Content-Transfer-Encoding: base64' . $rn;
        $content .= 'Content-Disposition: attachment' . $rn . $rn;
        $content .= chunk_split(base64_encode($mailData['file']['content']));
        $content .= '--PHP-mixed-' . $boundary . '--' . $rn;

        $header = implode($rn, array_map(function ($v, $k) {
            return $k . ': ' . $v;
        }, $mailData['header'], array_keys($mailData['header'])));

        mail($mailData['to'], $mailData['subject'], $content, $header);
    }
}
