<?php

	function pagination($arr) {
		$text_first = '|&lt;';
		$text_last = '&gt;|';
		$text_next = '&gt;';
		$text_prev = '&lt;';
		
		$total = $arr['total'];
		$page = $arr['page'] < 1 ? 1 : $arr['page'];
		$pagesize = !(int)$arr['limit'] ? 10 :$arr['limit'];
		$semiLinks = $arr['semiLinks'];
		$num_links = $semiLinks*2;
		$num_pages = ceil($total / $pagesize);
		
		$output = '<ul class="pagination">';
		$index = 1;
		if ($page > $semiLinks+1) {
			$output .= '<li id="jumpPage'.$index.'" value="1" ><a>' . $text_first . '</a></li>';
			$index++;
			$prev = ($page-$semiLinks)>0 ? $page-$semiLinks : 1;
			$output .= '<li id="jumpPage'.$index.'" value="'.$prev.'"><a>' . $text_prev . '</a></li>';
			$index++;
		}

		if ($num_pages > 1) {
			if ($num_pages <= $num_links) {
				$start = 1;
				$end = $num_pages;
			} else {
				$start = $page - floor($num_links / 2);
				$end = $page + floor($num_links / 2);

				if ($start < 1) {
					$end += abs($start) + 1;
					$start = 1;
				}

				if ($end > $num_pages) {
					$start -= ($end - $num_pages);
					$end = $num_pages;
				}
			}

			for ($i = $start; $i <= $end; $i++) {
				if ($page == $i) {
					$output .= '<li class="active"><span>' . $i . '</span></li>';
				} else {
					$output .= '<li id="jumpPage'.$index.'" value="'.$i.'"><a>' . $i . '</a></li>';
					$index++;
				}
			}
		}

		if ($page+$semiLinks < $num_pages) {
			$next = ($page+$semiLinks) < $num_pages ? $page+$semiLinks : $num_pages;
			$output .= '<li id="jumpPage'.$index.'" value="'.$next.'"><a>' . $text_next . '</a></li>';
			$index++;
			$output .= '<li id="jumpPage'.$index.'" value="'.$num_pages.'"><a>'. $text_last . '</a></li>';
		}

		$output .= '</ul>';

		if ($num_pages > 1) {
			$arr[1] = $output;
		} else {
			$arr[1] = '';
		}
		$arr[2] = '每页显示;<input type="text" value="'.$pagesize.'" name="pagesize" size="4">条&nbsp;&nbsp;'
				   .sprintf('显示 %d 到 %d / %d (总 %d 页)', ($total) ? (($page - 1) * $pagesize) + 1 : 0, ((($page - 1) * $pagesize) > ($total - $pagesize)) ? $total : ((($page - 1) * $pagesize) + $pagesize), $total, ceil($total / $pagesize))
				   .'&nbsp;&nbsp;<input type="text" value='.$page.' id="page" name="page" size="2">&nbsp;&nbsp;
				   		<button type="button" id="button-jump" class="btn btn-primary pull-right">
				   			<i class="fa fa-arrow-right"></i>'.'跳转'
				   		.'</button>';;
		return $arr;
	}
	
	function getTreeHtml($tree,&$selected){
		$str = '';
		foreach ($tree as $key=>$val){
			$str .= '<li><label><input type="checkbox" name="selected[]" value="'.$val['val'].'"';
			if (in_array($val['val'], $selected)){
				$str .= 'checked="checked"';
			}
			$str .='/>'.$val['name'].'</label>';
			if (isset($val['menu']) && !empty($val['menu'])){
				$str .= '<ul>'.getTreehtml($val['menu'],$selected).'</ul>';
			}
			$str .= '</li>';
		}
		return $str;
	}
?>
