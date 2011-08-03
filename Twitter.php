<?php

class Twitter {
	
	private $doc;
	private $tweets;
	private $userLink;
	
	public function __construct($user, $num) {
		$this->doc = new DOMDocument();
		$this->tweets = array();
		if ($this->doc->load('http://twitter.com/statuses/user_timeline/' . $user . '.rss?count=' . $num)) {
			$i = 0;
			foreach ($this->doc->getElementsByTagName('item') as $node) {
				$tweet = array(status => $this->getStatus($node), pDate => $this->getDate($node));
				$this->tweets[$i] = $tweet;
				if ($i == 0) $this->userLink = $node->getElementsByTagName('link')->item(0)->nodeValue;
				$i++;
			}
		}
	}
	
	private function getStatus($node) {
		$tweet = $node->getElementsByTagName('title')->item(0)->nodeValue;
		#remove user name
		$tweet = substr($tweet, stripos($tweet, ':') + 1);
		#find urls and convert to links
		$tweet = preg_replace('@(https?://([-\w\.]+)+(:\d+)?(/([\w/_\.]*(\?\S+)?)?)?)@', '<a href="$1">$1</a>', $tweet);
		#link replies
		$tweet = preg_replace("/@([0-9a-zA-Z]+)/", "<a href=\"http://twitter.com/$1\">@$1</a>", $tweet);
		return $tweet;
	}
	
	private function getDate($node) {
		$date = $node->getElementsByTagName('pubDate')->item(0)->nodeValue;
		#strip the +0000 from the end
		return substr($date, 0, strpos($date, '+0000'));
	}
	
	public function getTweets() {
		return $this->tweets;
	}
	
	public function getLink($msg) {
		return '<a href="' . $this->userLink .'">' . $msg . '</a>';
	}
	
}
