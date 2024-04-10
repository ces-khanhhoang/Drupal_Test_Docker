(function () {

  const $panel = document.querySelector('#ckeditor-ui-colors-panel');
  const $storage = document.querySelector('#colors-data-store');
  const $new_color = document.querySelector('#ckeditor-ui-new-color-panel');

  let model = [];

  if ($panel && $storage && $new_color) {


    model = initModel();
    renderUIModel();
    bindAddNewColor();

  }

  function initModel() {
    const rawData = $storage.getAttribute('data-colors') || '[]';
    let data;

    try {
      data = JSON.parse(rawData);
      $storage.value = rawData;
    } catch (e) {
      data = [];
      $storage.value = '[]';
    }

    return data;
  }

  function storeModel() {
    try {
      $storage.value = JSON.stringify(model);
    } catch (e) {
      console.log(e);
    }
  }

  function addColor(clr) {
    model = model.filter((curr) => {
      return curr.color !== clr.color;
    });
    model.push(clr);
    renderUIModel();
    storeModel();
  }

  function removeColor(hex) {
    model = model.filter((curr) => {
      return curr.color !== hex;
    });
    renderUIModel();
    storeModel();
  }


  function renderUIModel() {
    const $template = $panel.querySelector('template#color-template');
    const $target = $template.parentElement;

    const $colors = $panel.querySelectorAll('.color');
    $colors.forEach(($color) => {
      $color.remove();
    });

    model.forEach((clr) => {

      const $color = $template.content.querySelector('.color').cloneNode(true);

      $color.setAttribute('data-hex', clr.color);
      $color.querySelector('.label').innerText = clr.label || '';
      $color.style.backgroundColor = clr.color;

      $target.append($color);
    });

    bindUI();
  }


  function bindUI() {
    const $colors = $panel.querySelectorAll('.color');
    $colors.forEach(($color) => {
      const $action = $color.querySelector('.delete-action');
      if ($action) {
        $action.removeEventListener('click', () => {
        });
        $action.addEventListener('click', (e) => {
          e.preventDefault();
          removeColor($color.getAttribute('data-hex'));
        });
      }
    });
  }

  function bindAddNewColor() {

    const $hex = $new_color.querySelector('#hex');
    const $label = $new_color.querySelector('#color-label');
    const $submit = $new_color.querySelector('.form-submit');


    $submit.addEventListener('click', (e) => {
      e.preventDefault();

      const color = $hex.value;
      const label = $label.value;

      if (color) {
        addColor({
          color,
          label
        });
        $hex.value = '#000000';
        $label.value = '';

      }
    });

  }

})();
