(function () {
  function onlyDigits(v) { return String(v || '').replace(/\D/g, ''); }

  function formatIntBR(v) {
    v = onlyDigits(v);
    if (!v) return '';
    v = v.replace(/^0+(?=\d)/, '');
    return v;
  }

  function formatDecimalBR(v, casas) {
    v = onlyDigits(v);
    if (!v) return '';
    while (v.length < (casas + 1)) v = '0' + v;

    const dec = v.slice(-casas);
    let intPart = v.slice(0, -casas);

    intPart = intPart.replace(/^0+(?=\d)/, '');
    intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    return intPart + ',' + dec;
  }

  function formatMoneyBR(v) { return formatDecimalBR(v, 2); }
  function formatDecimal2BR(v) { return formatDecimalBR(v, 2); }

  function formatPercentBR(v) {
    // 0,00 até 100,00
    const txt = formatDecimalBR(v, 2);
    if (!txt) return '';
    const num = parseFloat(txt.replace(/\./g, '').replace(',', '.')) || 0;
    const capped = Math.min(Math.max(num, 0), 100);
    // volta para BR mantendo 2 casas
    const s = capped.toFixed(2).replace('.', ',');
    const parts = s.split(',');
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    return parts.join(',');
  }

  function formatPhoneBR(v) {
    v = onlyDigits(v);
    if (!v) return '';
    if (v.length <= 10) {
      // (00) 0000-0000
      v = v.slice(0, 10);
      return v.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3').replace(/-$/, '');
    }
    // (00) 00000-0000
    v = v.slice(0, 11);
    return v.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3').replace(/-$/, '');
  }

  function formatDocBR(v) {
    v = onlyDigits(v);
    if (!v) return '';
    if (v.length <= 11) {
      v = v.slice(0, 11);
      // 000.000.000-00
      return v
        .replace(/^(\d{3})(\d)/, '$1.$2')
        .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
        .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
    }
    v = v.slice(0, 14);
    // 00.000.000/0000-00
    return v
      .replace(/^(\d{2})(\d)/, '$1.$2')
      .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
      .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
      .replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d{1,2})/, '$1.$2.$3/$4-$5');
  }

  function bind(selector, formatter) {
    document.querySelectorAll(selector).forEach((el) => {
      const apply = () => {
        const old = el.value;
        const next = formatter(old);
        el.value = next;
      };

      el.addEventListener('input', apply);
      el.addEventListener('blur', apply);

      // aplica ao carregar (edit forms)
      apply();
    });
  }

  // Dinheiro / decimais / inteiros
  bind('.money-br', formatMoneyBR);
  bind('.decimal-br', formatDecimal2BR);
  bind('.int-br', formatIntBR);

  // Percentual / telefone / documento
  bind('.percent-br', formatPercentBR);
  bind('.phone-br', formatPhoneBR);
  bind('.doc-br', formatDocBR);
})();
