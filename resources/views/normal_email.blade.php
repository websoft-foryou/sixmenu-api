<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <title>QR Code</title>
</head>
<body style="margin: 0;background-color: #f1f1f1;font-family: 'Nunito Sans', sans-serif;">


<center>
    <table style="width: 600px;margin: 0 auto;">
        <tbody>
        <tr >
            <td style="height: 70px;text-align:center;background-color: #333;">

            </td>
        </tr>
        <tr>
            <td style="height: 480px;background-color: white;vertical-align: top;text-align: left;padding: 20px;color: #444;">
                <p style="font-size: 26px;font-weight: 600;text-align: center;color: #222;">Hello</p>
                <p style="font-size: 20px;font-weight: 600;">How are you?</p>
                <p style="line-height: 20px;">We send our restaurant QR code.</p>
                <p style="line-height: 20px;">Restaurant Name: {{$restaurant}}</p>
{{--                <p style="line-height: 20px;">--}}
{{--                    <img src="{{ $message->embedData($qrcode, 'qrcode.png') }}" width="290" height="290" alt="QRCode" style="display:inline-block" />--}}
{{--                    <img src="{{ $qrcode }}" width="290" height="290" alt="QRCode" style="display:inline-block" />--}}
{{--                </p>--}}
                <p style="padding: 30px 0px;">(Note: Please use attachment file.)</p>
                <p style="padding: 30px 0px;">Thanks & Best Regards</p>
            </td>
        </tr>
        <tr style="height: 20px;text-align: center;background-color: #333;padding: 5px;color: #ddd;">
            <td style="height: 20px;text-align: center;background-color: #333;padding: 5px;color: #ddd;">sixmenu team</td>
        </tr>
        </tbody>
    </table>
</center>
</body>
