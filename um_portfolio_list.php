<?php
$sub_menu = "400200";
include_once ('./_common.php');

auth_check ( $auth [$sub_menu], 'r' );

// INSERT
if ($_POST ['port'] == "add") {
	
	$img_name = $_FILES ['img'] ['name'];
	$upload_img = $_SERVER ['DOCUMENT_ROOT'] . "/uploads/image/" . basename ( $img_name );
	
	// 파일 정상 업로드시
	if ((move_uploaded_file ( $_FILES ['img'] ['tmp_name'], $upload_img ))) {
		
		$sql_max = sql_query ( " SELECT MAX(wr_order) AS max_order FROM `um_portfolio` " );
		$row_max = sql_fetch_array ( $sql_max );
		$max_order = $row_max ['max_order'] + 1;
		
		sql_query ( "insert `um_portfolio` set  wr_order ='" . $max_order . "', wr_name ='" . $_POST ['name'] . "',  wr_name2 ='" . $_POST ['name2'] . "',
		wr_type='" . $_POST ['type'] . "', wr_img= '" . $img_name . "',wr_ment='" . $_POST ['ment'] . "',wr_movie='" . $_POST ['url'] . "',
		wr_year='" . date ( "Y-m-d H:i:s" ) . "' 
		" );
		
		alert ( "학생 등록 완료" );
	} else
		alert ( "이미지는 반드시 올려주세요" );
}

// DELETE
if ($_POST ['port'] == "del") {
	
	if (isset ( $_POST ['chk'] ) && is_array ( $_POST ['chk'] )) {
		for($i = 0; $i < count ( $_POST ['chk'] ); $i ++) {
			$dh_id = $_POST ['chk'] [$i];
			
			sql_query ( " delete from `um_portfolio` where id = '" . $dh_id . "' ", true );
		}
		
		alert ( "삭제완료" );
	}
}

// 순위변경
if ($_POST ['port'] == "order_change") {
	
	if ($_POST ['ordertype'] == "up") {
		
		$change_no = $_POST ['changenum'] - 1;
		$order = sql_fetch ( " select * from `um_portfolio` where wr_order='" . $change_no . "' " );
		
		if ($order ['wr_order'] == $change_no) {
			sql_query ( " update `um_portfolio` set wr_order=wr_order+1 where id='" . $order ['id'] . "' " );
		}
		
		sql_query ( " update `um_portfolio` set wr_order=wr_order-1 where id='" . $_POST ['ordernum'] . "' " );
	} else if ($_POST ['ordertype'] == "down") {
		
		$change_no = $_POST ['changenum'] + 1;
		$order = sql_fetch ( " select * from `um_portfolio` where wr_order='" . $change_no . "' " );
		
		if ($order ['wr_order'] == $change_no) {
			sql_query ( " update `um_portfolio` set wr_order=wr_order-1 where id='" . $order ['id'] . "' " );
		}
		
		sql_query ( " update `um_portfolio` set wr_order=wr_order+1 where id='" . $_POST ['ordernum'] . "' " );
	}
}

// UPDATE
if ($_POST ['port'] == "update") {
	?>
<form action="um_portfolio_update.php" method="post"></form>

<?php
	
	sql_query ( "update `um_portfolio` set wr_name ='" . $_POST ['subject_id'] . "' where id = '" . $_POST ['con_id'] . "' " );
	alert ( "수정완료" );
}

if ($_GET ['stype']) {
	$stx = $_GET ['stype'];
	$sfl = "wr_type";
}

$sql_common = " from `um_portfolio` a ";
$sql_search = " where (1) ";

if ($stx) {
	$sql_search .= " and ( ";
	switch ($sfl) {
		case "pp_word" :
			$sql_search .= " ({$sfl} like '{$stx}%') ";
			break;
		case "wr_type" :
			$sql_search .= " ({$sfl} = '{$stx}') ";
			break;
		default :
			$sql_search .= " ({$sfl} like '%{$stx}%') ";
			break;
	}
	$sql_search .= " ) ";
}

if (! $sst) {
	$sst = "wr_order";
	$sod = "asc";
}
$sql_order = " order by {$sst} {$sod} ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch ( $sql );
$total_count = $row ['cnt'];

$rows = 20;
$total_page = ceil ( $total_count / $rows ); // 전체 페이지 계산
if ($page < 1) {
	$page = 1;
} // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query ( $sql );

$listall = '<a href="' . $_SERVER ['PHP_SELF'] . '" class="ov_listall">전체목록</a>';

$g5 ['title'] = '포트폴리오';
include_once ('./admin.head.php');

$colspan = 9;

/* 타입 추가하기------------------------- */
if ($_POST ['type_port'] == "addtype") {
	$sql_type_add = $_POST ['type_add'];
	sql_query ( "insert `um_portfolio_type` set type= '" . $sql_type_add . "'" );
}

?>




<style>
.tbox {
	padding: 10px 15px;
	background: #444;
	color: #FFF;
	font-weight: bold;
	margin: 10px 20px
}

.tbox a {
	color: #FFF;
	font-weight: normal
}

.tbox_tbl {
	width: 97%;
	margin: 0 auto
}

.tbox_tbl tr td {
	text-align: left;
	padding-left: 20px;
	font-size: 12px;
	height: 50px;
	border: 0;
	background: #f1f1f1
}

.typebox {
	border: 1px solid #CCC;
	padding: 10px 15px;
	margin: 0 20px
}

.typebox>span {
	display: block;
	margin-top: 10px;
	border-top: 1px solid #CCC;
	padding-top: 15px;
	color: #444
}

.ahit {
	color: yellow !important
}
</style>
<!-- 포트폴리오 타입추가 ------------------------------------------------------->

<div class="tbox" style="margin-top: 40px">포트폴리오 타입 추가</div>

<div class="typebox">
	<form method="post" id="type_form" enctype="multipart/form-data">
		<input type="hidden" name="type_port" value="addtype" /> 타입이름 : <input
			type="text" name="type_add"> <input type="submit" value="추가하기">
	</form>
	<span><B>현재 타입종류 : </B>
	<?php
	$typesql2 = sql_query ( " select * from `um_portfolio_type` " );
	for($k = 0; $type2 = sql_fetch_array ( $typesql2 ); $k ++) {
		echo $type2 ['type'] . ", ";
	}
	
	?>
	</span>
</div>






<div class="tbox" style="margin-top: 40px">포트폴리오 추가</div>

<!-- 인서트 -->
<div class="tbl_head02 tbl_wrap">

	<form method="post" id="add_form" enctype="multipart/form-data">
		<input type="hidden" name="port" value="add" />
		<table class="tbl03">
			<thead>
				<tr style="height: 40px">
					<th scope="col">Image</th>
					<th scope="col">Type</th>
					<th scope="col">Name</th>
					<th scope="col">subName</th>
					<th scope="col">MoiveURL</th>
					<th scope="col" width="35%">Memo</th>
					<th scope="col">UPDATE</th>
				</tr>
			</thead>
			<tbody>
				<!-- 인서트 -->
				<tr style="height: 95px; text-align: center">
					<td><input type="file" name="img" class="frm_input"
						style="width: 200px"></td>
					<td><select name="type" id="add_type">
							<option value="">선택하세요</option>
				<?php
				$typesql = sql_query ( " select * from `um_portfolio_type` " );
				for($k = 0; $type = sql_fetch_array ( $typesql ); $k ++) {
					?>
				<option value="<?php echo $type['type'] ?>"><?php echo $type['type'] ?></option>
				<?php } ?>
			</select></td>
					<td><input type="text" id="add_name" name="name" class="frm_input"
						style="width: 100px"></td>
					<td><input type="text" name="name2" class="frm_input"
						style="width: 80px"></td>
					<td><textarea name="url" rows="4" style="padding: 10px"></textarea></td>
					<td><textarea name="ment" rows="4" style="padding: 10px"></textarea></td>
					<td>
						<!--<input type="submit" value="추가" style="color:white; background:black;  "/>-->
						<a href="#" onclick="add_form();">추가</a>
					</td>
				</tr>
			</tbody>
		</table>
	</form>
</div>


<script>
function add_form() {
	if ($("#add_name").attr("value") == "") {
		alert("이름을 입력 해주세요.");
		$("#add_name").focus();
		return false;
	} else if ($("#add_type").attr("value") == "") {
		alert("타입을 선택 해주세요.");
		$("#add_type").focus();
		return false;
	} else {
		$("#add_form").submit();
	}

}
</script>




<!-- ============================ 포트폴리오 수정 및 삭제 -->

<div class="tbox">포트폴리오 리스트 (전체:<?php echo $total_count ?>) &nbsp;|&nbsp; <a
		href="./um_portfolio_list.php"
		<?php if(!$_GET['stype']) echo " class='ahit'" ?>>전체보기</a> 

<?php
$sql_type_for = sql_query ( "select * from `um_portfolio_type`" );

for($z = 0; $sql_type_job = sql_fetch_array ( $sql_type_for ); $z ++) {
	?>
	| <a
		href="./um_portfolio_list.php?stype=<?php echo $sql_type_job['type']?>"
		<?php if($_GET['stype'] == $sql_type_job['type']) echo " class='ahit'" ?>><?php echo $sql_type_job['type']?></a>
<?php
}
?>	
</div>






<form id="camp_list" method="post">
	<input type="hidden" id="port_id" name="port" value=""> <input
		type="hidden" id="con_id" name="con_id" value=""> <input type="hidden"
		id="subject_id" name="subject_id" value=""> <input type="hidden"
		id="ordertype" name="ordertype" value=""> <input type="hidden"
		id="ordernum" name="ordernum" value=""> <input type="hidden"
		id="changenum" name="changenum" value="">
	<div class="tbl_head02 tbl_wrap">
		<table>

			<thead>
				<tr style="height: 40px">
					<th scope="col"><input type="checkbox" name="chkall" value="1"
						id="chkall" onclick="check_all(this.form)"></th>
					<th scope="col">Num</th>
					<th scope="col">Image</th>
					<th scope="col">Date</th>
					<th scope="col">Name</th>
					<th scope="col">subName</th>
					<th scope="col">Type</th>
					<th scope="col" width="100">MoviUrl</th>
					<th scope="col" width="300">Memo</th>
				</tr>
			</thead>
	<?php for ($i=1; $row=sql_fetch_array($result); $i++) { ?>
	<tbody>
				<tr style="height: 60px; text-align: center">
					<td><input type="checkbox" name="chk[]"
						value=<?php echo $row['id']?> id="chk_0"></td>
					<td width="40"> <?php echo $i?>
			<span class="order_submit" data-num="<?php echo $row['id']; ?>"
						data-order="<?php echo $row['wr_order']; ?>"
						style="cursor: pointer" value="up"> ▲</span> <span
						class="order_submit" data-num="<?php echo $row['id']; ?>"
						data-order="<?php echo $row['wr_order']; ?>"
						style="cursor: pointer" value="down"> ▼</span>
					</td>
					<!-- 회차 wr_order-->
					<td width="150">
			<?php if ($row['wr_img']) { ?>
			<img width="100" height="100"
						src="/uploads/image/<?php echo $row['wr_img']; ?>">
			<?php } ?>
		</td>
					<td><?php echo $row['wr_year'] ?></td>
					<td><?php echo $row['wr_name'] ?></td>
					<td><?php echo $row['wr_name2'] ?></td>

					<td width="150"><?php echo $row['wr_type'] ?></td>

					<td> <?php echo $row["wr_movie"]?> </td>
					<td> <?php echo $row["wr_ment"] ?></td>

			<?php } ?>

	</tr>
			</tbody>
		</table>
	</div>

	<div class="btn_list01 btn_list">
		<button type="button" onclick="location.href='./um_list.php'"
			style="padding: 7px 20px; border: 1px soild #CCC">목록</button>
		<button id="sel_del" type="submit"
			style="padding: 7px 20px; border: 1px soild #CCC">선택삭제</button>


	</div>


</form>


<script>
function box_open(btype, bid) {
	$(".detail_box").hide();
	$("#box_"+btype+"_"+bid).show();
}

function box_close() {
//	$('.satisfy_box').hide();
	location.reload();
}



// 순위 변경
$(".order_submit").click(function() {

	$otype = $(this).attr("value");
	$("#ordertype").attr("value",$otype);

	$idx = $(this).attr("data-num");
	$idx2 = $(this).attr("data-order");
	$("#ordernum").attr("value",$idx);
	$("#changenum").attr("value",$idx2);
	$("#port_id").attr("value","order_change");
	$("#camp_list").submit();

});


$(function() {

	$("#sel_del, #sel_up").click(function(){
		var this_what = $(this).attr("id");
		var text_what = "";

		if (this_what == "sel_del") {

			$("#port_id").attr("value", "del");
			text_what = "삭제";
		}
		else if (this_what == "sel_up") {

			$("#port_id").attr("value", "update");
			text_what = "수정";
		}

		$('#camp_list').submit(function() {

			if(confirm("정말 "+text_what+"하시겠습니까?")) {

				if (!is_checked("chk[]")) {

					alert("선택"+text_what+" 하실 항목을 하나 이상 선택하세요.");
				    return;
				}

				return true;
			} 
			else {
				return false;
			}
		});
	});

});
</script>



<?php
include_once('./admin.tail.php');
?>