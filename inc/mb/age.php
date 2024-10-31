<div class="field field-age wrap-select">
  <label class="field-title title-strong" for="pp_ages">Age Requirement</label>
 
  <i class="fas fa-sort-down"></i>
  <select id="pp_ages" name="ages">
      <option value="16+" <?php echo '16+' === $ages ? 'selected="selected"': ''; ?>>16+</option>
      <option value="18+" <?php echo '18+' === $ages ? 'selected="selected"': ''; ?>>18+</option>
      <option value="21+" <?php echo '21+' === $ages ? 'selected="selected"': ''; ?>>21+</option>
      <option value="Teen Only" <?php echo 'Teen Only' === $ages ? 'selected="selected"': ''; ?>>Teen Only</option>
      <option value="All Ages" <?php echo 'All Ages' === $ages ? 'selected="selected"': ''; ?>>All Ages</option>
  </select>
</div>