<?php
/* @var $this yii\web\View */

?>

<table class="table my-table table-hover" >
    <thead>
    <tr style="background-color: #dfdfdf;text-align: center">
        <?php if(isset($header)){
            foreach($header as $key => $value){
                echo "<td align='center'>$value</td>";

            }
        }?>
    </tr>
    </thead>
    <tbody>
    <?php if(isset($results)){
        foreach ($results as $value) {
            ?>
            <tr>
                <?php
                if (isset($header)) {
                    foreach ($header as $h_key => $h_value) {
                        echo "<td align='center'>$value[$h_key]</td>";
                    }
                }
                ?>
            </tr>
            <?php
        } }
    ?>
    </tbody>
</table>