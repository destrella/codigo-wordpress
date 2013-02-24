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

/********************************************************/
/** Agrega tipos MIME para el administrador multimedia **/
/********************************************************/
add_filter('upload_mimes', 'custom_upload_mimes');
function custom_upload_mimes($mimes=array())
{
    $mimes['mp4'] = 'video/mp4';
    $mimes['webm'] = 'video/webm';
    $mimes['srt'] = 'text/plain';
    return $mimes; 
}

/******************************/
/* Reordena el menú del admin */
/******************************/
add_filter('custom_menu_order', 'return_true');
function return_true()
{
    /* Activa el orden personalizado del menú */
    return true;
}

add_filter('menu_order', 'nuevo_orden_menu');
function nuevo_orden_menu($menu)
{
    /* Descomentar línea siguiente para ver el contenido del menú en el admin */
    //echo('<pre>'.print_r($menu, true).'</pre>');
    
    /* Arrego con el nuevo orden */
    $mi_menu=array();
    /* Primera parte: antes del primer separador */
    $mi_menu[]='index.php';
    $mi_menu[]='separator1';
    /* Segunda parte: antes del segundo separador */
    $mi_menu[]='edit.php';
    $mi_menu[]='edit.php?post_type=page';
    $mi_menu[]='edit-comments.php';
    $mi_menu[]='upload.php';
    $mi_menu[]='separator2';
    /* Tercera parte: antes del último separador */
    $mi_menu[]='themes.php';
    $mi_menu[]='plugins.php';
    $mi_menu[]='users.php';
    $mi_menu[]='tools.php';
    $mi_menu[]='options-general.php';
    $mi_menu[]='separator-last';
    
    /* Recolecta menus no cubiertos en $mi_menu
       ej: los creados por plugins  */
    $al_final=array_diff($menu, $mi_menu);
    
    /* Devuelve el nuevo menú con los recolectados al final */
    return array_merge($mi_menu, $al_final);
}
?>