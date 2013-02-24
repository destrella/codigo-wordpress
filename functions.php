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
    
    /* Arreglo con el nuevo orden */
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

/********************************************************************/
/** Combina archivos CSS                                           **/
/** Paso 1: combina archivos del encabezado en un nuevo archivo.   **/
/** Paso 2: combina archivos al pie al final del archivo anterior. **/
/********************************************************************/

/* Paso 1: Archivos del encabezado */
add_action('wp_enqueue_scripts', 'combina_archivos_css', 99999);
function combina_archivos_css()
{
    /* variable con los estilos a usar */
    global $wp_styles;
    $contenido='';
    /* espacios en blanco a quitar */
    $blancos=array("\r\n", "\r", "\n", "\t", '  ', '    ');
    /* expresión regular para buscar comentarios */
    $comentarios='!/\*[^*]*\*+([^/][^*]*\*+)*/!';
    /* expresión regular para buscar urls */
    $busca_urls='/url\((.*?)\)/';
    /* ruta */
    $ruta=get_theme_root().'/'.get_template().'/';
    /* arreglo para el contenido de los archivos css */
    $css=array();
    $css[]=file_get_contents($ruta.'css/formato.css');
        
    /* procesa los estilos agregados manualmente */
    foreach($css as $c):
        /* define contenedor temporal */
        $cont_tmp='';
        /* elimina exceso de espacios en blanco */
        $cont_tmp=str_replace($blancos, ' ', $c);
        /* quita los comentarios */
        $cont_tmp=preg_replace($comentarios, '', $cont_tmp);
        /* agrega el contenido */
        $contenido.=trim($cont_tmp)."\n";
    endforeach;
    
    /* procesa los estilos agregados por plugins */
    foreach ($wp_styles->queue as $k=>$v):
        if($wp_stlyes->registered[$v]->handle==$v):
            /* define contenedor temporal */
            $cont_tmp='';
            
            if(strpos($wp_styles->registered[$v]->src, 'http')===false):
                /* si es una ruta relativa */
                $cont_tmp=file_get_contents($_SERVER['DOCUMENT_ROOT'].$wp_styles->registered[$v]->src);
            else:
                /* si es una ruta absoluta */
                $cont_tmp=file_get_contents($wp_styles->registered[$v]->src);
            endif;
            
            /* elimina exceso de espacios en blanco */
            $cont_tmp=str_replace($blancos, ' ', $cont_tmp);
            
            /* busca urls en los estilos */
            preg_match_all($busca_urls, $cont_tmp, $matches, PREG_SET_ORDER);
            
            /* procesa las url encontradas */
            foreach ($matches as $k=>$m):
                /* comprueba que no sea url absoluta ni datos */
                if(strpos($m[0], 'data:')===false && strpos($m[0], 'http')===false):
                    /* quita las comillas en caso de tener */
                    $m[1]=trim($m[1], '"\'');
                    /* quita el punto y diagonal iniciales en caso de tener */
                    $m[1]=trim($m[1], './');
                    /* crea la ruta absoluta */
                    $m[1]=dirname($wp_styles->registered[$v]->src).'/'.$m[1];
                    /* reemplaza la ruta relativa con la absoluta */
                    $cont_tmp=str_replace($m[0], 'url('.$m[1].')', $cont_tmp);
                endif;
            endforeach;
            
            /* agrega el contenido */
            $contenido.=$cont_tmp."\n";
            /* quita el estilo de la lista */
            wp_dequeue_style($wp_styles->registered[$v]->handle);
            
        endif;
    endforeach;
    
    /* escribe el contenido en un archivo */
    file_put_contents($ruta.'formato.css', $contenido, LOCK_EX) or die('Error al escribir el archivo');
    
    /* registra el archivo */
    wp_register_style('tema', get_template_directory_uri().'/estilos.css', false, date('His'), 'all');
    wp_enqueue_style('tema');
}

/* Paso 2: Archivos registrados al pie */
add_action('wp_footer', 'combina_archivos_css_pie', 19);
function combina_archivos_css_pie()
{
    /* variable con los estilos a usar */
    global $wp_styles;
    $contenido='';
    /* espacios en blanco a quitar */
    $blancos=array("\r\n", "\r", "\n", "\t", '  ', '    ');
    /* expresión regular para buscar urls */
    $busca_urls='/url\((.*?)\)/';
    /* ruta */
    $archivo=get_theme_root().'/'.get_template().'/estilos.css';
    
    foreach($wp_styles->queue as $k=>$v):
        /* comprueba que no sea el estilo ¡creado anteriormente! */
        if($wp_styles->registered[$v]->handle==$v && 'tema'!=$v):
            /* define contenedor temporal */
            $cont_tmp='';
            
            if(strpos($wp_styles->registered[$v]->src, 'http')===false):
                $cont_tmp=file_get_contents($_SERVER['DOCUMENT_ROOT'].$wp_styles->registered[$v]->src);
            else:
                $cont_tmp=file_get_contents($wp_styles->registered[$v]->src);
            endif;
            
            /* elimina exceso de espacios en blanco */
            $cont_tmp=str_replace($blancos, ' ', $cont_tmp);
            
            /* busca urls en los estilos */
            preg_match_all($busca_urls, $cont_tmp, $matches, PREG_SET_ORDER);
            
            /* procesa las url encontradas */
            foreach ($matches as $k=>$m):
                /* comprueba que no sea url absoluta ni datos */
                if(strpos($m[0], 'data:')===false && strpos($m[0], 'http')===false):
                    /* quita las comillas en caso de tener */
                    $m[1]=trim($m[1], '"\'');
                    /* quita el punto y diagonal iniciales en caso de tener */
                    $m[1]=trim($m[1], './');
                    /* crea la ruta absoluta */
                    $m[1]=dirname($wp_styles->registered[$v]->src).'/'.$m[1];
                    /* reemplaza la ruta relativa con la absoluta */
                    $cont_tmp=str_replace($m[0], 'url('.$m[1].')', $cont_tmp);
                endif;
            endforeach;
            
            /* agrega el contenido */
            $contenido.=$cont_tmp."\n";
            /* quita el estilo de la lista */
            wp_dequeue_style($wp_styles->registered[$v]->handle);
            
        endif;
        
    endforeach;
    
    /* escribe el contenido al final del archivo previamente creado */
    file_put_contents($archivo, $contenido, FILE_APPEND | LOCK_EX) or die('Error al escribir en el archivo');
}
?>