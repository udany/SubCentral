<?php
function Send(){
	/** @var AutoResponder[] $autoResponders */
	$autoResponders = AutoResponder::Select([new Filter('Active', 1)]);

	foreach($autoResponders as $autoResponder){
		$autoResponder->SendAll();
	}
}