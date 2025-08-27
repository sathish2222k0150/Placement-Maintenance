<div class="row">
  <div class="col-md-12 mb-3">
    <label class="form-label">Address</label>
    <input name="address" required class="form-control"
      value="<?= htmlspecialchars($form['address'] ?? '') ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Village</label>
    <input name="village" required class="form-control"
      value="<?= htmlspecialchars($form['village'] ?? '') ?>">
  </div>
  <div class="col-md-4 mb-3">
    <label class="form-label">Mandal / Taluk</label>
    <input name="mandal" required class="form-control"
      value="<?= htmlspecialchars($form['mandal'] ?? '') ?>">
  </div>
  <div class="col-md-4 mb-3">
  <label class="form-label">District</label>
  <select name="district" required class="form-select">
    <?php
      $districts = [
        'Ariyalur', 'Chengalpattu', 'Chennai', 'Coimbatore', 'Cuddalore',
        'Dharmapuri', 'Dindigul', 'Erode', 'Kallakurichi', 'Kancheepuram',
        'Karur', 'Krishnagiri', 'Madurai', 'Mayiladuthurai', 'Nagapattinam',
        'Namakkal', 'Nilgiris', 'Perambalur', 'Pudukkottai', 'Ramanathapuram',
        'Ranipet', 'Salem', 'Sivaganga', 'Tenkasi', 'Thanjavur',
        'Theni', 'Thoothukudi', 'Tiruchirappalli', 'Tirunelveli', 'Tirupathur',
        'Tiruppur', 'Tiruvallur', 'Tiruvannamalai', 'Tiruvarur', 'Vellore',
        'Viluppuram', 'Virudhunagar'
      ];

      foreach ($districts as $d) {
          $selected = (($form['district'] ?? '') == $d) ? 'selected' : '';
          echo "<option value=\"$d\" $selected>$d</option>";
      }
    ?>
  </select>
</div>

</div>