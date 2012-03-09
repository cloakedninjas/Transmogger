<?php
class App_View_Helper_Toplist extends Zend_View_Helper_Abstract {

	public function topList($rows, $index_start) {
		$html = '';
		$i = 0;

		foreach ($rows as $row) {
			$i++;
			$rank = $i + $index_start;
			$row->label = ($row->label) ? $row->label : 'Untitled';

			$html .= '<tr>';
			$html .= '<td>' . $rank . '</td>';
			$html .= '<td><a href="/' . $row->ref . '">' . $row->label . '</a></td>';
			$html .= '<td>' . $row->rating_score . '</td>';
			$html .= '<td>' . $row->rating_votes . '</td>';
			$html .= '<td>' . $this->formatDate($row->created) . '</td>';
			$html .= '</tr>';
		}
		return $html;
    }

    protected function formatDate($date) {
		$time = strtotime($date);
		$age = time() - $time;

		if ($age < 60) {
			return "just now";
		}
		elseif ($age < 300) {
			return "a few minutes ago";
		}
		elseif ($age < 3600) {
			return "in the last hour";
		}
		elseif ($age < 86400) {
			$hours = ceil($age / 3600);
			return "$hours hours ago";
		}
		elseif ($age < 172800) {
			return "yesterday";
		}
		elseif ($age < 604800) {
			return date("l", $time);
		}
		elseif ($age < 2592000) {
			$days = ceil($age / 86400);
			return "$days days ago";
		}
		else {
			return date("d F", $time);
		}
	}
}