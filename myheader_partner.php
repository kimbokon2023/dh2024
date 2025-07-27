<?php
$WebSite = "https://dh2024.co.kr/";	

if($_SESSION['level'] == '10')
	$partnername = "del_list";
if($_SESSION['level'] == '9')
	$partnername = "plist";
 
?>
 
<form id="eworks_board_form" name="eworks_board_form" method="post" enctype="multipart/form-data" >	 
   
	<input type="hidden" id="eworksPage" name="eworksPage" value="<?=$eworksPage?>" > 
	<input type="hidden" id="e_viewexcept_id" name="e_viewexcept_id" value="<?=$e_viewexcept_id?>" >   <!-- 전자결재 보기 제한 -->	
	<input type="hidden" id="e_num" name="e_num" value="<?=$e_num?>" > 
	<input type="hidden" id="ripple_num" name="ripple_num" value="<?=$ripple_num?>" > 
	<input type="hidden" id="SelectWork" name="SelectWork" value="<?=$SelectWork?>" > 
	<input type="hidden" id="eworksel" name="eworksel" value="<?=$eworksel?>" >    <!-- 전자결재 진행상태  draft send -->	
	<input type="hidden" id="choice" name="choice" value="<?=$choice?>" >    <!-- 전자결재 진행상태  draft send -->		
	<input type="hidden" id="approval_right" name="approval_right" value="<?=$approval_right?>" >   
	<input type="hidden" id="status" name="status" value="<?=$status?>" >   
	<input type="hidden" id="done" name="done" value="<?=$done?>" >    <!-- 전자결재 진행상태  done -->		
	<input type="hidden" id="author_id" name="author_id" value="<?=$author_id?>" > 
	
	<!-- 전자결재 관련 배열 -->	
	<input id="numid_arr" name="numid_arr[]" type=hidden >
	<input id="registdate_arr" name="registdate_arr[]" type=hidden >
	<input id="eworks_item_arr" name="eworks_item_arr[]" type=hidden >
	<input id="author_arr" name="author_arr[]" type=hidden >
	<input id="author_id_arr" name="author_id_arr[]" type=hidden >
	<input id="e_title_arr" name="e_title_arr[]" type=hidden >
	<input id="e_line_id_arr" name="e_line_id_arr[]" type=hidden >
	<input id="e_line_arr" name="e_line_arr[]" type=hidden >
	<input id="r_line_arr" name="r_line_arr[]" type=hidden >		   
	<input id="r_line_id_arr" name="r_line_id_arr[]" type=hidden >		   
	<input id="e_confirm" name="e_confirm" type=hidden >		   
	<input id="e_confirm_arr" name="e_confirm_arr[]" type=hidden >		   
	<input id="e_confirm_id" name="e_confirm_id" type=hidden >		   
	<input id="e_confirm_id_arr" name="e_confirm_id_arr[]" type=hidden >		   
	
 <?php if($chkMobile==false) { ?>
	<div class="container">     
 <?php } else { ?>
 	<div class="container-fluid">     
	<?php } ?>	
  
<div class="row d-flex">        
    <div class="col-sm-2 justify-content-center">        	
		<a href="<?=$WebSite?>motor/<?=$partnername?>.php">
			 <img src="<?=$WebSite?>img/dhlogo.png" style="width:75%;" >
		 </a>	
	</div>
<div class="col-sm-10 justify-content-center">     
	<nav class="navbar navbar-expand navbar-custom ">
	<div class="navbar-nav ">   
            <div class="nav-item" id="home-menu">
		
            </div>						

            <div class="nav-item dropdown flex-fill">			 
                <!-- 드롭다운 메뉴-->
                 <a class="nav-link  dropdown-toggle fs-5" href="#"  data-toggle="dropdown" >  <i class="bi bi-person-fill-down"></i>  <?=$_SESSION["name"]?>님 환영합니다!  </a>                
                <div class="dropdown-menu">                   
                    <a class="dropdown-item  " href="<?$root_dir?>/login/logout.php"> <ion-icon name="arrow-redo-outline"></ion-icon> 로그아웃 </a>                    
                    <a class="dropdown-item  " href="<?$root_dir?>/member/updateForm_partner.php?id=<?=$_SESSION["userid"]?>"> <ion-icon name="basket-outline"></ion-icon> 정보수정 </a>                    
						
                </div>
            </div>				

			
			</div>		
		</nav>      
  </div>  			
</div>  	
  
<?php 

?>
  
	  
</div>


</form>
