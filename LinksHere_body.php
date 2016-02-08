<?php
/* 
 * What Links Here function
 * 
 */


class LinksHere {


	public function executeLinksHere($parser, $page, $delim = ",", $linked=false, $underscore=false, $filter=NULL, $count=NULL, $start=NULL, $end=NULL) {

		$title = Title::newFromText($page);
		$output = "";
	
		if ((get_class($title)=="Title") && ($title->exists())) { 

			$pagens = $title->getNamespace();
	        $pagename =  $title->getText();

			$listLinks = array();

			$listLinks = array_unique($this->queryLinksHere($pagename, $pagens, $linked, $underscore, $filter));
			if ($count != NULL) {
				return(count($listLinks));
			}
			
			if ($start != NULL) {
				if (is_numeric($start)) {
					$listLinks = array_slice($listLinks, $start);
				}
			}
			if ($end != NULL) {
				if (is_numeric($end)) {
					$listLinks = array_slice($listLinks, 0, $end);
				}				
			}
			
			$output = join($delim, $listLinks);
			if ($linked) {
				return($output);
			}
			else {
				return $parser->insertStripItem( $output, $parser->mStripState );
			}
		}
		
		else {return($output);}
	}

	public function executeLinksOut($parser, $page, $delim = ",", $linked=false, $underscore=false, $filter=NULL, $count=NULL, $start=NULL, $end=NULL) {

		$title = Title::newFromText($page);
		$output = "";

		if ((get_class($title)=="Title") && ($title->exists())) {

			$pageid = $title->getArticleID();

			$listLinks = array();

			$listLinks = array_unique($this->queryLinksOut($pageid, $linked, $underscore, $filter));
		
			if ($count != NULL) {
				return(count($listLinks));
			}
		
			if ($start != NULL) {
				if (is_numeric($start)) {
					$listLinks = array_slice($listLinks, $start);
				}
			}
			
			if ($end != NULL) {
				if (is_numeric($end)) {
					$listLinks = array_slice($listLinks, 0, $end);
				}				
			}
	
			$output = join($delim, $listLinks);
			if ($linked) {
					return($output);
			}
			else {
					return $parser->insertStripItem( $output, $parser->mStripState );
			}
		}

		else {return($output);}

	}

	public function executeLinksInOut($parser, $page, $delim = ",", $linked=false, $underscore=false, $filter=NULL, $count=NULL, $start=NULL, $end=NULL) {

		$title = Title::newFromText($page);
        $output = "";
		
		if ((get_class($title)=="Title") && ($title->exists())) {

			$pagens = $title->getNamespace();
			$pagename =  $title->getText();			
			$pageid = $title->getArticleID();

			$listLinksIn = array();
			$listLinksOut = array();
			$listLinksInOut = array();

			$listLinksIn = array_unique($this->queryLinksHere($pagename, $pagens, $linked, $underscore, $filter));
			$listLinksOut = array_unique($this->queryLinksOut($pageid, $linked, $underscore, $filter));

			$listLinksInOut = array_intersect($listLinksIn, $listLinksOut);			
			if ($count != NULL) {
                return(count($listLinksInOut));
            }
			
			if ($start != NULL) {
				if (is_numeric($start)) {
					$listLinksInOut = array_slice($listLinksInOut, $start);
				}
			}
			if ($end != NULL) {
				if (is_numeric($end)) {
					$listLinksInOut = array_slice($listLinksInOut, 0, $end);
				}				
			}
			
			$output = join($delim, $listLinksInOut);
			if ($linked) {
					return($output);
			}
			else {
					return $parser->insertStripItem( $output, $parser->mStripState );
			}
		}

		else {return($output);}

	}

    public function executeLinksInOrOut($parser, $page, $delim = ",", $linked=false, $underscore=false, $filter=NULL, $count=NULL, $start=NULL, $end=NULL) {

		$title = Title::newFromText($page);
		$output = "";

		if ((get_class($title)=="Title") && ($title->exists())) {

			$pagens = $title->getNamespace();
			$pagename =  $title->getText();
			$pageid = $title->getArticleID();
			$listLinksIn = array();
			$listLinksOut = array();
			$listLinksInOrOut = array();
			#echo $pageid;
			#echo "<br />\n";
			$listLinksIn = array_unique($this->queryLinksHere($pagename, $pagens, $linked, $underscore, $filter));
			$listLinksOut = array_unique($this->queryLinksOut($pageid, $linked, $underscore, $filter));
			#echo count($listLinksOut);
			#echo"<br />\n";

			$listLinksInOrOut = array_unique(array_merge($listLinksIn, $listLinksOut));
			if ($count != NULL) {
				return(count($listLinksInOrOut));
			}
			sort($listLinksInOrOut);

			if ($start != NULL) {
				if (is_numeric($start)) {
					$listLinksInOrOut = array_slice($listLinksInOrOut, $start);
				}
			}
			if ($end != NULL) {
				if (is_numeric($end)) {
					$listLinksInOrOut = array_slice($listLinksInOrOut, 0, $end);
				}				
			}

			$output = join($delim, $listLinksInOrOut);
			if ($linked) {
				return($output);
			}
			else {
				return $parser->insertStripItem( $output, $parser->mStripState );
			}
		}

		else {return($output);}

    }	


	private function queryLinksHere ($pagename, $pagens, $linked, $underscore, $filter) {

		global $wgContLang;
		$pagename = str_replace(" ", "_", $pagename);

		#DEFINE FILTER
		$filterarray = array();
		if ($filter!=NULL) {
			$filterarray = explode(",", $filter);
			$this->cleanarray($filterarray);
		}

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			array( 'pagelinks', 'page' ),
			array( 'page_namespace', 'page_title' ),
			array(
				'pl_from=page_id' ,
				'pl_title' => $pagename,
				'pl_namespace' => $pagens
			),
			__METHOD__,
			array( 'ORDER BY' => 'page_namespace, page_title' )
		);

		$i = 0;
		$listLinks=array();
		
		foreach( $res as $row ) {

			#Let's see if we have filters
			if (count($filterarray) > 0) {
				$namespace = $wgContLang->getNsText($row->page_namespace);
				if ($namespace =="") {$namespace = ":";}
				$detect = 0;
				if (in_array($namespace, $filterarray)) {$detect++;}
				if ($detect == 0) {continue;}
			}
			#Print Namespace in Title, omit if Main
			$namespace = $wgContLang->getNsText($row->page_namespace);
			if ($namespace == "") {
				if ( $pagename != $row->page_title ) {
					$listLinks[$i] = $row->page_title;
				}
			} else {
				if ( ( $pagens.":".$pagename != $row->page_namespace.":".$row->page_title ) ) {
					$listLinks[$i] = $wgContLang->getNsText($row->page_namespace).":".$row->page_title;
				}
			}
			if (!$underscore && $listLinks[$i]!= '') {
				$listLinks[$i] = str_replace("_", " ", $listLinks[$i]); 
			}
			if ($linked && $listLinks[$i]!= '') {
				$listLinks[$i] = "[[".$listLinks[$i]."]]";
			}			

			$i++;

		}

        return($listLinks);

	}

	private function queryLinksOut ($pageid, $linked, $underscore, $filter) {

		global $wgContLang;

		// Get values;
		$title = Title::newFromID( $pageid );
		$pagename = $title->getText();
		$pagens = $title->getNamespace();
		$pagename = str_replace(" ", "_", $pagename);

		#DEFINE FILTER
		$filterarray = array();
		if ($filter!=NULL) {
			$filterarray = explode(",", $filter);
			$this->cleanarray($filterarray);
		}
		
		$dbr = wfGetDB( DB_SLAVE );
		
		$res = $dbr->select(
			array( 'pagelinks' ),
			array( 'pl_namespace', 'pl_title' ),
			array(
				'pl_from' => $pageid
			),
			__METHOD__,
			array( 'ORDER BY' => 'pl_namespace, pl_title' )
		);

		
		$i = 0;
		$listLinks=array();

		foreach( $res as $row ) {

			#Let's see if we have filters
			if (count($filterarray) > 0) {
					$namespace = $wgContLang->getNsText($row->pl_namespace);
					if ($namespace =="") {$namespace = ":";}
					$detect = 0;
					if (in_array($namespace, $filterarray)) {$detect++;}
					if ($detect == 0) {continue;}
			}

			#Print Namespace in Title, omit if Main
			$namespace = $wgContLang->getNsText($row->pl_namespace);
			#echo $row->pl_title."<br />\n";
			if ($namespace == "") {
				if ( $pagename != $row->pl_title ) {
					$listLinks[$i] = $row->pl_title;
				}
			} else {
				if ( ( $pagens.":".$pagename != $row->pl_namespace.":".$row->pl_title ) ) {
					$listLinks[$i] = $wgContLang->getNsText($row->pl_namespace).":".$row->pl_title;
				}
			}
			if (!$underscore && $listLinks[$i]!= '') {
					$listLinks[$i] = str_replace("_", " ", $listLinks[$i]);
			}
			if ($linked && $listLinks[$i]!= '') {
					$listLinks[$i] = "[[".$listLinks[$i]."]]";
			}

			$i++;
        }
		
		return($listLinks);

	}

	private function cleanarray (array $array) {
		
		$newarray = array();

		foreach ($array as $arrvalue) {
			array_push($newarray, trim($arrvalue));
		}
		return($newarray);
	}

}


?>
