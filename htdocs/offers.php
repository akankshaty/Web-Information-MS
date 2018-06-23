<?php 
	session_start();
	include('header.php');
	include('auth.php');
	
	if ($_SESSION['u_role'] != "Student") {
		header("Location: index.php");
	}
	

?>
<div class="main-content">
	<?php 
		$res = mysqli_query($conn,"SELECT * FROM settings_option WHERE setting_name='Accepting Offer Letter Deadline'");
		if(mysqli_num_rows($res) > 0) {
			$unformatted_deadline = mysqli_fetch_assoc($res)['setting_value'];
			$deadline = date("l jS, F Y h:i:s A",$unformatted_deadline);
			$deadline_passed = $unformatted_deadline < time()? true : false; 			
		} else {
			$deadline = 'not set.';
			$deadline_passed = false;
		}

		echo '<h1>Your Offer Letters</h1>';
		echo '<p style="line-height:2em;">Here\'s a list of offer letters you have received so far. You may only accept one offer. Once you accept one offer, that action cannot be undone and all other offers would be declined automatically. So choose wisely. <br />The deadline to accept an offer is <span class="font_bold" style="text-decoration: underline;">'.$deadline.'</span></p>';

		echo '<table class="entries">
		<thead>
			<tr>
				<th>Project Name</th>
				<th>Role Name</th>
				<th>Accept/Decline Offer</th>

			</tr>';
			// Get a list of all offer letters sent to this student
			$offers = mysqli_query($conn, "SELECT * FROM offer_letter_requests WHERE student_email='".$_SESSION['u_name']."'");
			while($row1 = mysqli_fetch_assoc($offers)) { // Prints out each row from offer_letter_requests db table results one by one
				echo '<tr>';
					echo '<td>';
					echo $row1['project_name'];
					echo '</td>';
					echo '<td>';
					echo $row1['role_name'];
					echo '</td>';
					echo '<td>';
					if($row1['status'] == "Pending") {
						if (!$deadline_passed) {
							echo '<img id="accept'.$row1['student_email'].'" name="'.$row1['student_email'].'-*'.$row1['project_name'].'" src="images/accept.png" style="margin: 10px 15px 10px 0;" />';
							echo '<img id="decline'.$row1['student_email'].'" name="'.$row1['student_email'].'-*'.$row1['project_name'].'" src="images/decline.png" style="margin: 10px 0 10px 15px;" />';							
						} else {
							echo '<img src="images/accept.png" style="margin: 10px 15px 10px 0;filter: grayscale(100%);" />';
							echo '<img src="images/decline.png" style="margin: 10px 0 10px 15px;filter: grayscale(100%);" />';
							echo '<br /><span style="color: gray;">Sorry, the deadline to accept/decline offers has passed!</span>';
						}

					} else if ($row1['status'] == "Declined") {
						echo "<p class='font_red font_bold' style='margin:25px;'>Declined!</p>";
					} else if ($row1['status'] == "Accepted") {
						echo "<p class='font_green font_bold' style='margin:25px;'>Accepted!</p>";
					}
					echo '</td>';
				echo '</tr>';
			}
			if (mysqli_num_rows($offers) < 1) { // If student received no offer letters
				echo '<tr>';
					echo '<td colspan="10">';
					echo 'No offer letters received.';
					echo '</td>';
				echo '</tr>';
			}
			echo '</thead>
				</table><br />';
		
?>
</div>
<script>
$(".error").hide();
$(".survey").hide();
$(document).ready(function() {
	$(".toggle_project_form").on("click", function() {
		$(".error").hide();
		$(".s"+$(this).attr("id")).toggle();
	});
	$("form[class*=new_vacancy]").submit(function(e) {
		if (!$("."+$(this).attr("class")+" input[name=total_seats]").val()) {
			$(".error_txt_cls").text("Please enter the total number of seats available for this role.");
			$(".error").show();
			return false;
		} 
		if (!confirm('Are you sure you want to add a vacancy for this role with these values? The values cannot be changed later.')) {
			return false;
		}
	});
	$("img[id^=accept]").mouseenter(function(e) {
		$(this).attr("src","images/accept_hover.png");
		$(this).css("cursor","pointer");
	});
	$("img[id^=accept]").mouseleave(function(e) {
		$(this).attr("src","images/accept.png");
	});	
	$("img[id^=decline]").mouseenter(function(e) {
		$(this).attr("src","images/decline_hover.png");
		$(this).css("cursor","pointer");
	});
	$("img[id^=decline]").mouseleave(function(e) {
		$(this).attr("src","images/decline.png");
	});
	$("img[id^=accept]").click(function(e) {
		if (confirm('Are you sure you want to accept this offer and join this project? All other offers will be automatically declined and this action cannot be undone.')) {
			var v_name = $(this).attr("name");
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "accept_offer": v_name },
				success: function(response){
					window.location.replace("offers.php");
				}
			});
		}
	});
	$("img[id^=decline]").click(function(e) {
		if (confirm('Are you sure you want to decline this offer? This action cannot be undone.')) {
			var v_name = $(this).attr("name");
			$.ajax({
				url: "update_info.php",
				type: "POST",
				data: { "decline_offer": v_name },
				success: function(response){
					window.location.replace("offers.php");
				}
			});
		}
	});
});
</script>

<?php
	include('footer.php');
	mysqli_close($conn);
?>