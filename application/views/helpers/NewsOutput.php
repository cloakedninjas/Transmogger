<?php
class App_View_Helper_Newsoutput extends Zend_View_Helper_Abstract {

	public function newsOutput($news) {
		$html = '<div id="newsflash"><span class="title">News:</span>';
		
		foreach ($news as $n) {
			$date = date('d M Y', strtotime($n->date));
			$html .= '<p><a href="/content/news">' . $n->title . '</a><span class="date">' . $date . '</span></p>';
		}
				
		$html .= '</div>';
		
		$html .= '
		<script type="text/javascript">newsFlash.init();</script>
		';
		
		return $html;
    }

}