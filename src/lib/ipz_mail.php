<?php   
/*
iPuzzle.WebPieces
Copyright (C) 2004 David Blanchard

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

function sendMailFromTable($sender, $subject="", $body="", $action="SEND", $show_body=false, $alert_on_send=false) {

	if(empty($action)) $action=="SEND";

	if($action=="PREVIEW") {
		echo $body;
		exit;
	} elseif($action=="SEND") {
		$from=$sender;
		$reply_to=$from;
		$return_to=$sender;
		
		$date=getdate();
		$day=substr($date["weekday"], 0, 3);
		$mday="0".$date["mday"];
		$mday=substr($mday, strlen($mday)-2, 2);
		$mon=substr($date["month"], 0, 3);
		$year=$date["year"];
		$time=$date["hours"].":".$date["minutes"].":".$date["seconds"];
		
		$headers= "Return-Path: $return_to\r\n";
		$headers.="X-Sieve: Server Sieve 2.2\r\n";
		$headers.="Date: $day, $mday $mon $year $time +0100\r\n";
		//$headers.="Message-Id: $message_id\r\n";
		$headers.="From: $from\r\n";
		$headers.="Reply-to: $reply_to\r\n";
		$headers.="X-Sender: $sender\r\n";
		$headers.="X-Mailer: PHP\r\n";
		$headers.="X-Priority: 1\r\n";
		$headers.="MIME-Version: 1.0\r\n";
		$headers.="Content-Type: text/html; charset=iso-8859-1\r\n";
	
		if($show_body) echo $body;
		if($alert_on_send) echo jsAlert("Le message a été envoyé.");
		
		//$sql="select * from subscribers";
		$sql="select * from subscribers where sub_id not in (select sb.sub_id from newsltr_history as nh, subscribers as sb where nh.sub_id<>sb.sub_id and nh.nl_id=$nl_id)";
		$stmt = $cs->query($sql);
		while($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
			$to=$rows["sub_email"];
			$sub_id=$rows["sub_id"];
			$sql="insert into newsltr_history (nl_id, sub_id) values($nl_id, $sub_id)";
			$result2=$cs->query($sql);
			mail($to, $subject, $message, $headers);
		}
	}

}

function sendMail($sender, $recipient, $subject="", $body="", $action="SEND", $show_body=false, $alert_on_send=false) {

	if(empty($action)) $action=="SEND";

	if($action=="PREVIEW") {
		echo $body;
		exit;
	} elseif($action=="SEND") {
		$from=$sender;
		$reply_to=$from;
		$return_to=$sender;
		
		$date=getdate();
		$day=substr($date["weekday"], 0, 3);
		$mday="0".$date["mday"];
		$mday=substr($mday, strlen($mday)-2, 2);
		$mon=substr($date["month"], 0, 3);
		$year=$date["year"];
		$time=$date["hours"].":".$date["minutes"].":".$date["seconds"];
		
		$headers= "Return-Path: $return_to\r\n";
		$headers.="X-Sieve: Server Sieve 2.2\r\n";
		$headers.="Date: $day, $mday $mon $year $time +0100\r\n";
		//$headers.="Message-Id: $message_id\r\n";
		$headers.="From: $from\r\n";
		$headers.="Reply-to: $reply_to\r\n";
		$headers.="X-Sender: $sender\r\n";
		$headers.="X-Mailer: PHP\r\n";
		$headers.="X-Priority: 1\r\n";
		$headers.="MIME-Version: 1.0\r\n";
		$headers.="Content-Type: text/html; charset=iso-8859-1\r\n";
	
		if($show_body) echo $body;
		//if($alert_on_send) echo jsAlert("Le message a été envoyé.");
		if($alert_on_send) echo "<p>Le message a été envoyé.</p>";
		
		mail($recipient, $subject, $body, $headers);
	}

}

?>
