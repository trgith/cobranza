<?php
/**
 * Created by PhpStorm.
 * User: FIREWORLD
 * Date: 08/03/2017
 * Time: 07:18 PM
 */
?>

    <script src="<?php echo $_layoutParams['route_js'];?>front.js"></script>

    <!-- se pueden poner scirpts propios de js -->
    <?php if (isset($_layoutParams['js']) && count($_layoutParams['js'])):
        foreach ($_layoutParams['js'] as $js):?>
            <script src="<?php echo $js; ?>" type="text/javascript"></script>
        <?php endforeach;
    endif; ?>

    </body>
</html>
