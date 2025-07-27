<?php
require_once($_SERVER['DOCUMENT_ROOT'] . "/session.php");  

if(!isset($_SESSION["level"]) || $_SESSION["level"]>5) {
	sleep(1);
	header("Location:" . $WebSite . "login/login_form.php"); 
	exit;
}   

include $_SERVER['DOCUMENT_ROOT'] . '/load_header.php';

$title_message = '매입처 업로드'

?>

 <title> <?=$title_message?> </title> 
 </head>

<body>

 <?php 
    
$sql=" select * from " . $DB . ".phonebook_buy " ; 
$tablename = 'phonebook_buy';  
	  	  
 
?>		 

<div class="container-fluid">

<div class="card-header">    
 <h6> <?=$title_message?> &nbsp; &nbsp; 		
 <button  type="button" class="btn btn-dark btn-sm" id="savegridBtn"> <i class="bi bi-check-square-fill"></i> 일괄등록 실행 </button>	 &nbsp; &nbsp; &nbsp; 
 <button  type="button" class="btn btn-dark btn-sm" onclick="self.close();"  > <i class="bi bi-x-lg"></i> 창닫기 </button>	 &nbsp;
 
 </h6>
</div> 
  
   
<form name="regform" id="regform"  method="post" >  

  
   <input id="tablename" name="tablename" value='<?=$tablename?>'type=hidden >
   <input id="col1" name="col1[]" type=hidden >
   <input id="col2" name="col2[]" type=hidden >
   <input id="col3" name="col3[]" type=hidden >
   <input id="col4" name="col4[]" type=hidden >
   <input id="col5" name="col5[]" type=hidden >
   <input id="col6" name="col6[]" type=hidden >
   <input id="col7" name="col7[]" type=hidden >
   <input id="col8" name="col8[]" type=hidden >
   <input id="col9" name="col9[]" type=hidden >
   <input id="col10" name="col10[]" type=hidden >
   <input id="col11" name="col11[]" type=hidden >
   <input id="col12" name="col12[]" type=hidden >
   <input id="col13" name="col13[]" type=hidden >

<div class="container-fluid">  
	<div class="card mb-2 mt-2">  
	<div class="card-body">  	 
		
    <div class="input-group p-2 mb-2">
		<span style="margin-left:20px;font-size:20px;color:blue;"> ※ 해당셀 엑셀 내용을 복사 후 붙여넣기 </span>
       </div>
	 <div class="d-flex mt-2 mb-2">
		<div id="grid" style="width:1870px;">  
		
  </div>     
  </div>     
      
	 </form>
	 </div>   
   </div> 	   
  </div>
  </div> <!-- end of wrap -->  
   

<!-- 페이지로딩 -->
<script>
// 페이지 로딩
$(document).ready(function(){	
    var loader = document.getElementById('loadingOverlay');
    loader.style.display = 'none';
});
</script>   
   
<script>

$(document).ready(function(){
	
$("#searchBtn").click(function(){  document.getElementById('board_form').submit();   });		
 
 var total_sum=0; 
 var count=0;  // 전체줄수 카운트 
  
 var rowNum = 300; 
 
 const data = [];
 const columns = [];	
 const COL_COUNT = 13;

 for(i=0;i<rowNum + 1;i++) {			 
		 row = { name: i };		 
		 for (let k = 0; k < COL_COUNT; k++ ) {				
				row[`col1`] = '' ;						 						
				row[`col2`] = '' ;						 											 						
				row[`col3`] = '' ;						 											 						
				row[`col4`] = '' ;						 											 											 						
				row[`col5`] = '' ;						 											 											 						
				row[`col6`] = '' ;						 											 											 						
				row[`col7`] = '' ;						 						
                row[`col8`] = '' ;						 										
                row[`col9`] = '' ;						 										
                row[`col10`] = '' ;								 											 						
				row[`col11`] = '' ;						 						
				row[`col12`] = '' ;						 											 						
				row[`col13`] = '' ;						 											 						
				 										
						}
				data.push(row); 			
 }
 

 class CustomTextEditor {
	  constructor(props) {
		const el = document.createElement('input');
		const { maxLength } = props.columnInfo.editor.options;

		el.type = 'text';
		el.maxLength = maxLength;
		el.value = String(props.value);

		this.el = el;
	  }

	  getElement() {
		return this.el;
	  }

	  getValue() {
		return this.el.value;
	  }

	  mounted() {
		this.el.select();
	  }
	}	

const grid = new tui.Grid({
	  el: document.getElementById('grid'),
	  data: data,
	  bodyHeight: 700,					  					
	  columns: [ 				   
		{
		  header: '매입처코드',
		  name: 'col1',
		  sortingType: 'desc',
		  sortable: true,
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 80
			}			
		  },	 		
		  align: 'center'
		},			
		{
		  header: '매입처명',
		  name: 'col2',
		  width:200,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 80
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '대표자',
		  name: 'col3',
		  width: 100,
		  editor: {
			type: CustomTextEditor,
		  },	 		
		  align: 'center'
		},
		{
		  header: '주소1',
		  name: 'col4',
		  width:200,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '업태',
		  name: 'col5',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '종목',
		  name: 'col6',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '전화',
		  name: 'col7',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '모바일',
		  name: 'col8',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: 'Email',
		  name: 'col9',
		  width:200,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: 'Fax',
		  name: 'col10',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '담당자명',
		  name: 'col11',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '연락처',
		  name: 'col12',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		},
		{
		  header: '비고',
		  name: 'col13',
		  width:100,
		  editor: {
			type: CustomTextEditor,
			options: {
			  maxLength: 40
			}			
		  },	 		
		  align: 'center'
		}
	  ],
	columnOptions: {
			resizable: true
		  },
// rowHeaders: ['rowNum','checkbox'],   // checkbox 형성

	});		
	
var Grid = tui.Grid; // or require('tui-grid')
Grid.applyTheme('default', {
			  cell: {
				normal: {
				  background: '#fbfbfb',
				  border: '#e0e0e0',
				  showVerticalBorder: true
				},
				header: {
				  background: '#eee',
				  border: '#ccc',
				  showVerticalBorder: true
				},
				rowHeader: {
				  border: '#ccc',
				  showVerticalBorder: true
				},
				editable: {
				  background: '#fbfbfb'
				},
				selectedHeader: {
				  background: '#d8d8d8'
				},
				focused: {
				  border: '#418ed4'
				},
				disabled: {
				  text: '#b0b0b0'
				}
			  }	
	});	


	function savegrid() {		
								let col1    =  new Array();  
								let col2    =  new Array();  
								let col3    =  new Array();  
								let col4    =  new Array();  
								let col5    =  new Array();  
								let col6    =  new Array();  
								let col7    =  new Array();  
								let col8    =  new Array();  
								let col9    =  new Array();  
								let col10   =  new Array();  
								let col11   =  new Array();  
								let col12   =  new Array();  
								let col13   =  new Array();  
								
					        // console.log(grid.getRowCount());	//삭제시 숫자가 정상적으로 줄어든다.
						     const MAXcount=grid.getRowCount() ; 
							 let pushcount=0;
							 for(i=0;i<MAXcount;i++) {      // grid.value는 중간중간 데이터가 빠진다. rowkey가 삭제/ 추가된 것을 반영못함.    
							    if( grid.getValue(i, 'col1')!= null ) {				 								   
								    col1.push(swapcommatopipe(grid.getValue(i, 'col1')));																 
								    col2.push(swapcommatopipe(grid.getValue(i, 'col2')));																 
								    col3.push(swapcommatopipe(grid.getValue(i, 'col3')));																 
								    col4.push(swapcommatopipe(grid.getValue(i, 'col4')));																 
								    col5.push(swapcommatopipe(grid.getValue(i, 'col5')));																 
								    col6.push(swapcommatopipe(grid.getValue(i, 'col6')));																 
								    col7.push(swapcommatopipe(grid.getValue(i, 'col7')));																 
								    col8.push(swapcommatopipe(grid.getValue(i, 'col8')));																 
								    col9.push(swapcommatopipe(grid.getValue(i, 'col9')));																 
								    col10.push(swapcommatopipe(grid.getValue(i, 'col10')));																 
								    col11.push(swapcommatopipe(grid.getValue(i, 'col11')));																 
								    col12.push(swapcommatopipe(grid.getValue(i, 'col12')));																 																 
								    col13.push(swapcommatopipe(grid.getValue(i, 'col13')));																 																 
								   										
									}								   									
								 }	
								$('#col1').val(col1);					 
								$('#col2').val(col2);					 
								$('#col3').val(col3);					 
								$('#col4').val(col4);					 
								$('#col5').val(col5);					 
								$('#col6').val(col6);					 
								$('#col7').val(col7);					 
								$('#col8').val(col8);					 
								$('#col9').val(col9);					 
								$('#col10').val(col10);					 
								$('#col11').val(col11);					 
								$('#col12').val(col12);					 
								$('#col13').val(col13);					 
	

		 $.ajax({
					url: "upload.php",
					type: "post",		
					data: $("#regform").serialize(),
					dataType:"json",
					success : function( data ){
						console.log( data);
						
						Swal.fire(
						  '처리되었습니다.',
						  '데이터가 성공적으로 등록되었습니다.',
						  'success'
						)	

					setTimeout(function() { 
					   self.close();
					 window.opener.location.reload();  // 부모창 새로고침
					   }, 2000);								
						
					},
					error : function( jqxhr , status , error ){
						console.log( jqxhr , status , error );
					} 			      		
				   });
				
			
		   }	


$("#savegridBtn").click(function(){  savegrid();   });	  


});



function SearchEnter(){
    if(event.keyCode == 13){
		document.getElementById('board_form').submit(); 
    }
}



function swapcommatopipe(strtmp)
{
	let replaced_str = strtmp.replace(/,/g, '|');
	return replaced_str;	   
}



</script>

</body>
  </html>
