<html>

<head>
    <style>
        .html{
            overflow: scroll;
            width:300px;height:300px;
            font-size: 10px;
        }

        .screen_shot{
            max-width: 300px;
            max-height:; 300px;
        }

        .flag_true{
            background: green;
        }
        .flag_false{
            background: red;
        }

    </style>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
          crossorigin="anonymous">
</head>
<body>

<table class="table">
    <tr>
        <?php foreach($results as $testName=>$result){ ?>
            <td><?=$testName;?></td>
        <?php } ?>
    </tr>
    <tr>
        <?php foreach($results as $testName=>$result){ ?>
            <td class="flag_<?=$result['flag'];?>"><?=$result['flag'];?></td>
        <?php } ?>
    </tr>
    <tr>
        <?php foreach($results as $testName=>$result){ ?>
            <td><pre class="html"><?php
                    $html = htmlspecialchars(file_get_contents($result['html'][0]));
                    $html = preg_replace('/\s+/', ' ', $html);
                    $html = preg_replace('/&gt;/', "&gt;\n", $html);
                    echo $html;
                    ?></pre></td>
        <?php } ?>
    </tr>
    <tr>
        <?php foreach($results as $testName=>$result){ ?>
            <td style="width:300px">
                <?php foreach($result['screen_shots'] as $shot) {
                    printf('<img class="screen_shot" src="file://%s"  ><br>', $shot);
                }?>
            </td>
        <?php } ?>
    </tr>
</table>


</body>
</html>

