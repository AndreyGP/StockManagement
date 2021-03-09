<?php
/**
 * Created by UnityWorld Framework.
 * User: Andrei G. Pastushenko
 * Date: 04.01.2018
 * Time: 2:53
 */

if (gc_enabled()){
    gc_mem_caches();
}else{
    gc_enable();
    gc_mem_caches();
}