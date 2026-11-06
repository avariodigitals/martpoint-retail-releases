<?php
/**
 * Closes a mobile Oil & Gas screen: footer menu, copyright strip and the Assist
 * chat, plus the custom-select behaviour the mobile rules require (a native
 * option list would pop outside the app container).
 */
?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => $gas_nav ?? 'more']); ?>
  </div>

  <script>
  (function(){
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      sel.style.display = 'none';
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){ label.textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select'; }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx == sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation();
          sel.selectedIndex = idx;
          setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active');
          opts.classList.remove('open');
          sel.dispatchEvent(new Event('change'));
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });
    document.addEventListener('click', function(){
      document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); });
    });
  })();
  </script>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
