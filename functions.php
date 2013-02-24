<?php
/***********************************************/
/** Muestra enlace de regreso en una entrada. **/
/** ejemplo de uso:                           **/
/** echo '<p>'.enlace_regresar(false).'</p>'; **/
/***********************************************/
function enlace_regresar($echo=true, $formato='post')
{
    /* variable $post para tomar el número de id */
    global $post;
    
    /* el id del post al cual regresar */
    $id=$formato.'-'.$post->ID;
    
    /* toma el enlace de donde viene si existe */
    $el_enlace=!empty($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:false;
    
    /* comprueba que exista el enlace y que se esté en un post o página */
    if($el_enlace && is_single()):  
        /* divide la url para personalizar el texto de regreso */
        $parte=explode('/', $el_enlace);
        /* ejemplo de la url después del explode:
         * [0] => http:
         * [1] => 
         * [2] => ejemplo.com
         * [3] => ?s=texto
         */

        /* texto del enlace */
        $texto='&larr; Regresar';
        
        /* determina de donde viene para personalizar el texto de regreso */
        if(is_numeric($parte[3])):
            /* viene del calendario */
            $texto.=' al calendario';
        elseif(strstr($parte[3],'?s=')):
            /* viene de una búsqueda */
            $texto.=' a la búsqueda';
        endif;
        
        $enlace=
            '<a href="'.$el_enlace.'#'.$id.'" class="e_atras">'.
            $texto.
            '</a>';
        
        /* determina si se imprime o si se devuelve el valor */
        if($echo):
            echo $enlace;
        else:
            return $enlace;
        endif;

    else:
        return false;
    endif;
}

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

/********************************/
/** Reordena el menú del admin **/
/********************************/
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
    /* echo('<pre>'.print_r($menu, true).'</pre>'); */
    
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
     * ej: los creados por plugins
     */
    $al_final=array_diff($menu, $mi_menu);
    
    /* Devuelve el nuevo menú con los recolectados al final */
    return array_merge($mi_menu, $al_final);
}

/****************************************************************/
/** Agrega y/o reordena columnas de la lista de posts en admin **/
/****************************************************************/
add_filter('manage_posts_columns', 'columnas_de_entradas');
function columnas_de_entradas($columnas)
{
    /* Columnas por defecto:
     * $columnas['cb'] => '<input type="checkbox">';
     * $columnas['title'] => 'Título';
     * $columnas['author'] => 'Autor';
     * $columnas['categories'] => 'Categorías';
     * $columnas['tags'] => 'Etiquetas';
     * $columnas['comments'] => '<span class="vers"><div title="Comentarios" class="comment-grey-bubble"></div></span>';
     * $columnas['date'] => 'Fecha';
     */
    
    /* Reordena las columnas.
     * Omite la columna 'author'.
     * Agrega la columna 'Imagen destacada'.
     */
    $mis_columnas=array();
    $mis_columnas['cb']=$columnas['cb'];
    $mis_columnas['title']=$columnas['title'];
    $mis_columnas['categories']=$columnas['categories'];
    $mis_columnas['tags']=$columnas['tags'];
    $mis_columnas['img_destacada']='Imagen destacada';
    $mis_columnas['comments']=$columnas['comments'];
    $mis_columnas['date']=$columnas['date'];
    
    return $mis_columnas;
}
add_filter('manage_posts_custom_column', 'columnas_personalizadas');
function columnas_personalizadas($columna, $id)
{
    /* Procesa las columnas */
    switch ($columna):
        /* Columna de imagen destacada */
        case 'img_destacada'
            /* Imprime la miniatura */
            echo the_post_thumbnail('thumbnail');
            break;
    endswitch;
}
?>