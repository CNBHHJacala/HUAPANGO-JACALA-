<?php
require_once __DIR__ . '/config.php';
$pageTitle = 'Registro · ' . SITE_NAME;
include __DIR__ . '/header.php';
?>
<section class="hero">
  <div class="eyebrow">Registro oficial en línea</div>
<h1>Concurso Nacional de Huapango <br><span style="color:var(--turquesa)">Jacala de Ledezma</span></h1>

  <p>Registra tu pareja, realiza el pago de inscripción y recibe tu folio de participación. El número de pareja se asigna automáticamente al finalizar el registro.</p>
  


</section>

<main class="container">
<div class="card">
  <div class="card-head">
    <h2>Ficha de inscripción</h2>
  </div>
  <div class="card-body">
    <form action="registrar.php" method="post" autocomplete="off">
      <div class="section-title"><span>1</span> Datos del concurso</div>
      <div class="grid">
        <div class="field">
          <label for="categoria">Categoría *</label>
          <select id="categoria" name="categoria" required>
            <option value="">Selecciona una categoría</option>
            <?php foreach(CATEGORIES as $v): ?>
              <option value="<?=e($v)?>"><?=e($v)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field">
          <label for="estilo">Estilo *</label>
          <select id="estilo" name="estilo" required>
            <option value="">Selecciona un estilo</option>
            <?php foreach(STYLES as $v): ?>
              <option value="<?=e($v)?>"><?=e($v)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="field full">
          <label for="representacion">Representación *</label>
          <input id="representacion" name="representacion" maxlength="150" required placeholder="Municipio, comunidad, grupo o institución que representa">
        </div>
      </div>

      <div class="section-title"><span>2</span> Datos de la huapanguera</div>
      <div class="grid">
        <div class="field full">
          <label>Nombre completo *</label>
          <input name="huapanguera_nombre" maxlength="150" required>
        </div>
        <div class="field">
          <label>Fecha de nacimiento *</label>
          <input type="date" name="huapanguera_fecha_nacimiento" required>
        </div>
        <div class="field">
          <label>Teléfono *</label>
          <input name="huapanguera_telefono" maxlength="30" inputmode="tel" required>
        </div>
        <div class="field full">
          <label>Domicilio *</label>
          <input name="huapanguera_domicilio" maxlength="255" required>
        </div>
      </div>

      <div class="section-title"><span>3</span> Datos del huapanguero</div>
      <div class="grid">
        <div class="field full">
          <label>Nombre completo *</label>
          <input name="huapanguero_nombre" maxlength="150" required>
        </div>
        <div class="field">
          <label>Fecha de nacimiento *</label>
          <input type="date" name="huapanguero_fecha_nacimiento" required>
        </div>
        <div class="field">
          <label>Teléfono *</label>
          <input name="huapanguero_telefono" maxlength="30" inputmode="tel" required>
        </div>
        <div class="field full">
          <label>Domicilio *</label>
          <input name="huapanguero_domicilio" maxlength="255" required>
        </div>
      </div>

      <div class="notice">
        Al continuar se generará el número de pareja y folio. Después podrás realizar el pago de inscripción mediante Mercado Pago. El registro quedará confirmado únicamente cuando el pago sea validado por el sistema.
      </div>

      <div class="actions">
        <div class="price"><small>Inscripción por pareja</small>$<?=number_format(INSCRIPTION_PRICE,2)?> MXN</div>
        <button class="btn btn-primary" type="submit">Continuar con el registro →</button>
      </div>
    </form>
  </div>
</div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
