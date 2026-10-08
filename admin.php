<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
session_start();
if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }
$error=''; $message='';
if (!isset($_SESSION['admin'])) {
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u=(string)($_POST['user']??''); $p=(string)($_POST['password']??'');
  if(hash_equals(ADMIN_USER,$u)&&hash_equals(ADMIN_PASSWORD,$p)){session_regenerate_id(true);$_SESSION['admin']=true;header('Location: admin.php');exit;}
  $error='Usuario o contraseña incorrectos.';
 }
 $pageTitle='Administración'; include __DIR__.'/header.php'; ?>
 <main class="container"><div class="login card"><div class="card-body"><h2 style="font-family:'Playfair Display',serif;color:var(--vino)">Administración</h2><?php if($error):?><div class="error"><?=e($error)?></div><?php endif;?><form method="post"><div class="field"><label>Usuario</label><input name="user" required></div><br><div class="field"><label>Contraseña</label><input type="password" name="password" required></div><br><button class="btn btn-primary">Ingresar</button></form></div></div></main><?php include __DIR__.'/footer.php'; exit;
}
$pdo=db();
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'],$_POST['id'])) {
 $id=(int)$_POST['id']; $action=(string)$_POST['action'];
 if($action==='confirmar'){ $pdo->prepare("UPDATE parejas SET estado_pago='aprobado', estado_registro='confirmado', mp_status='aprobado_manual' WHERE id=?")->execute([$id]); $message='Registro confirmado manualmente.'; }
 if($action==='cancelar'){ $pdo->prepare("UPDATE parejas SET estado_registro='cancelado' WHERE id=?")->execute([$id]); $message='Registro cancelado.'; }
 if($action==='reactivar'){ $pdo->prepare("UPDATE parejas SET estado_registro=IF(estado_pago='aprobado','confirmado','pendiente_pago') WHERE id=?")->execute([$id]); $message='Registro reactivado.'; }
}
$q=trim((string)($_GET['q']??'')); $cat=trim((string)($_GET['categoria']??'')); $status=trim((string)($_GET['estado']??''));
$sql="SELECT * FROM parejas WHERE 1=1"; $args=[];
if($q!==''){ $sql.=" AND (folio LIKE ? OR representacion LIKE ? OR huapanguera_nombre LIKE ? OR huapanguero_nombre LIKE ? OR CAST(numero_pareja AS CHAR) LIKE ?)"; $like="%$q%"; array_push($args,$like,$like,$like,$like,$like); }
if($cat!=='' && in_array($cat,CATEGORIES,true)){ $sql.=" AND categoria=?"; $args[]=$cat; }
if(in_array($status,['pendiente_pago','confirmado','cancelado'],true)){ $sql.=" AND estado_registro=?"; $args[]=$status; }
$sql.=" ORDER BY id DESC LIMIT 1000"; $st=$pdo->prepare($sql);$st->execute($args);$rows=$st->fetchAll();
$stats=$pdo->query("SELECT COUNT(*) total, SUM(estado_registro='confirmado') confirmados, SUM(estado_registro='pendiente_pago') pendientes, SUM(estado_registro='cancelado') cancelados FROM parejas")->fetch();
$pageTitle='Panel administrativo'; include __DIR__.'/header.php'; ?>
<main class="container"><div class="hero" style="padding:0"><div class="eyebrow">Panel administrativo</div><h1>Inscripciones</h1><p>Administra las parejas registradas · <a href="admin.php?logout=1">Cerrar sesión</a></p></div>
<?php if($message):?><div class="notice"><?=e($message)?></div><?php endif;?>
<div class="admin-stats"><div><b><?=e((string)$stats['total'])?></b><span>Total</span></div><div><b><?=e((string)$stats['confirmados'])?></b><span>Confirmados</span></div><div><b><?=e((string)$stats['pendientes'])?></b><span>Pendientes</span></div><div><b><?=e((string)$stats['cancelados'])?></b><span>Cancelados</span></div></div>
<div class="card"><div class="card-body"><form class="admin-filters" method="get"><input name="q" value="<?=e($q)?>" placeholder="Buscar folio, pareja o representación"><select name="categoria"><option value="">Todas las categorías</option><?php foreach(CATEGORIES as $v):?><option <?=($cat===$v?'selected':'')?> value="<?=e($v)?>"><?=e($v)?></option><?php endforeach;?></select><select name="estado"><option value="">Todos los estados</option><option value="confirmado" <?=$status==='confirmado'?'selected':''?>>Confirmados</option><option value="pendiente_pago" <?=$status==='pendiente_pago'?'selected':''?>>Pendientes</option><option value="cancelado" <?=$status==='cancelado'?'selected':''?>>Cancelados</option></select><button class="btn btn-primary">Buscar</button><a class="btn btn-light" href="exportar.php?<?=http_build_query(['q'=>$q,'categoria'=>$cat,'estado'=>$status])?>">Exportar CSV</a></form>
<div class="table-wrap"><table class="admin-table"><thead><tr><th>#</th><th>Folio</th><th>Categoría / estilo</th><th>Representación</th><th>Pareja</th><th>Pago</th><th>Registro</th><th>Acciones</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><strong>#<?=e((string)$r['numero_pareja'])?></strong></td><td><?=e($r['folio'])?></td><td><?=e($r['categoria'])?><br><small><?=e($r['estilo'])?></small></td><td><?=e($r['representacion'])?></td><td><b><?=e($r['huapanguera_nombre'])?></b><br><?=e($r['huapanguero_nombre'])?></td><td><?=e($r['estado_pago'])?><br><small><?=e($r['mp_status']??'')?></small></td><td><span class="admin-status <?=e($r['estado_registro'])?>"><?=e($r['estado_registro'])?></span></td><td><div class="admin-actions"><a class="mini" href="editar.php?id=<?=$r['id']?>">Editar</a><?php if($r['estado_registro']==='confirmado'&&$r['estado_pago']==='aprobado'):?><a class="mini" href="boleta.php?folio=<?=urlencode($r['folio'])?>">Boleta</a><?php endif;?><?php if($r['estado_registro']==='pendiente_pago'):?><form method="post" onsubmit="return confirm('¿Confirmar manualmente este pago y registro?')"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="confirmar"><button class="mini" type="submit">Confirmar</button></form><?php endif;?><?php if($r['estado_registro']!=='cancelado'):?><form method="post" onsubmit="return confirm('¿Cancelar este registro?')"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="cancelar"><button class="mini danger" type="submit">Cancelar</button></form><?php else:?><form method="post"><input type="hidden" name="id" value="<?=$r['id']?>"><input type="hidden" name="action" value="reactivar"><button class="mini" type="submit">Reactivar</button></form><?php endif;?></div></td></tr><?php endforeach;?></tbody></table></div></div></div></main><?php include __DIR__.'/footer.php'; ?>
