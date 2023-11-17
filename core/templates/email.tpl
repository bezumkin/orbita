<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{$.env.SITE_NAME}</title>
    <style>
        body {
            background: #f7f7f7;
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            font-family: Arial, serif;
            font-size: 14px;
            color: #000;
        }
        table { border-spacing: 0; width: 100% }
        table td { margin: 0 }
        a { color: #369; outline: none; text-decoration: none }
        pre { padding: 10px; background: #f5f5f5; border-radius: 5px; white-space: pre-line }

        .main { width: 600px; margin: auto }
        .logo { padding: 20px 0; text-align: center }
        .content {
            height: 100px;
            vertical-align: top;
            background: #ffffff;
            border: 1px solid #e1ddcb;
            border-radius: 5px;
            padding: 30px;
        }
        .footer td { padding: 35px 0; }
        .footer td.center { text-align: center }
        .footer td.right { text-align: right }
        .footer a { color: #999; font-weight: bold }

        .preview { padding: 10px; background-color: #efefef; border-radius: 5px; }

        {block 'style'}{/block}
    </style>
</head>
<body>
<table class="main">
    <thead>
    <tr>
        <td class="logo">
            <a href="{$.env.SITE_URL}" target="_blank">
                <img src="{$.env.SITE_URL}email/logo.png" srcset="{$.env.SITE_URL}email/logo@2x.png 2x" alt="{$.env.SITE_NAME}" width="286" height="161" />
            </a>
        </td>
    </tr>
    </thead>
    <tbody>
    <tr>
        {block 'content-wrapper'}
            <td class="content">
                {block 'content'}{/block}
            </td>
        {/block}
    </tr>
    </tbody>
    <tfoot>
    <tr>
        <td>
            <table class="footer">
                <tr>
                    <td class="left"></td>
                    <td class="center"><a href="{$.env.SITE_URL}" target="_blank">{$.env.SITE_NAME}</a></td>
                    <td class="right"></td>
                </tr>
            </table>
        </td>
    </tr>
    </tfoot>
</table>
</body>
</html>