<?php
/***************************/
/** Agrega notas de autor **/
/***************************/
add_filter('admin_footer_text', 'pie_admin', 9999);
function pie_admin()
{
    /* reemplaza la línea "Desarrollado por" */
    echo '&nbsp;';
}

add_filter('update_footer', 'pie_version_admin', 9999);
function pie_version_admin()
{
    /* reemplaza la línea "Version" */
    return 'Desarrollado por: <a href="http://destrella.com.mx/" target="_blank">DEstrella.mx</a>';
}

add_filter('the_generator', 'copyright_generador');
function copyright_generador($generator)
{
    /* solo si no es el feed */
    if(!is_feed()):
        /* reemplaza la etiqueta "meta generator" */
        return '<meta name="generator" content="DEstrella.mx">';
    endif;
}


?>