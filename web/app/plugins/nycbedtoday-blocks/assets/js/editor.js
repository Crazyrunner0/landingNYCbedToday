(function (window, wp) {
  if (!window || !wp) {
    return;
  }

  var library = window.nycbedtodayBlockLibrary || {};
  if (!library.blocks || !Array.isArray(library.blocks)) {
    return;
  }

  var blocks = wp.blocks;
  var element = wp.element;
  var blockEditor = wp.blockEditor || wp.editor;
  var components = wp.components;
  var i18n = wp.i18n || {
    __: function (str) {
      return str;
    },
  };

  if (!blocks || !element || !blockEditor || !components) {
    return;
  }

  var registerBlockType = blocks.registerBlockType;
  var useBlockProps =
    blockEditor.useBlockProps ||
    function () {
      return {};
    };
  var RichText = blockEditor.RichText;
  var InspectorControls = blockEditor.InspectorControls;
  var PanelBody = components.PanelBody;
  var TextControl = components.TextControl;
  var TextareaControl = components.TextareaControl;

  var createElement = element.createElement;
  var Fragment = element.Fragment;

  var inspectorControlTypes = ['url', 'datetime'];

  function renderRichTextField(field, value, onChange) {
    var children = [];

    if (field.label) {
      children.push(
        createElement('span', { className: 'nycbedtoday-block-editor__label' }, field.label)
      );
    }

    children.push(
      createElement(RichText, {
        className: 'nycbedtoday-block-editor__richtext',
        tagName: field.tag || 'div',
        value: value,
        placeholder: field.placeholder || field.label || '',
        allowedFormats: [],
        onChange: onChange,
      })
    );

    return createElement(
      'div',
      { className: 'nycbedtoday-block-editor__field nycbedtoday-block-editor__field--richtext' },
      children
    );
  }

  function renderTextControl(field, value, onChange) {
    return createElement(TextControl, {
      label: field.label,
      value: value,
      onChange: onChange,
      placeholder: field.placeholder || '',
      type: field.control === 'url' ? 'url' : field.control === 'datetime' ? 'text' : 'text',
      help:
        field.control === 'datetime'
          ? i18n.__('Use ISO 8601 format, for example 2024-12-31T23:00:00', 'nycbedtoday-blocks')
          : undefined,
    });
  }

  function renderTextareaControl(field, value, onChange) {
    return createElement(TextareaControl, {
      label: field.label,
      value: value,
      onChange: onChange,
      placeholder: field.placeholder || '',
    });
  }

  function fieldControl(field, attributes, setAttributes) {
    var value = typeof attributes[field.name] === 'undefined' ? '' : attributes[field.name];
    var onChange = function (nextValue) {
      setAttributes(
        (function () {
          var updated = {};
          updated[field.name] = nextValue;
          return updated;
        })()
      );
    };

    if (field.control === 'richtext' && RichText) {
      return renderRichTextField(field, value, onChange);
    }

    if (field.control === 'textarea') {
      return renderTextareaControl(field, value, onChange);
    }

    return renderTextControl(field, value, onChange);
  }

  library.blocks.forEach(function (blockConfig) {
    var attributes = {};

    (blockConfig.fields || []).forEach(function (field) {
      attributes[field.name] = {
        type: 'string',
        default: typeof field.default === 'undefined' ? '' : field.default,
      };
    });

    registerBlockType(blockConfig.name, {
      title: blockConfig.title,
      description: blockConfig.description,
      icon: blockConfig.icon || 'layout',
      category: (library.category && library.category.slug) || 'widgets',
      supports: blockConfig.supports || {},
      attributes: attributes,
      edit: function (props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var blockProps = useBlockProps({
          className: blockConfig.className
            ? blockConfig.className + ' nycbedtoday-block'
            : 'nycbedtoday-block',
        });

        var fields = blockConfig.fields || [];
        var inspectorFields = fields.filter(function (field) {
          return inspectorControlTypes.indexOf(field.control) !== -1;
        });
        var canvasFields = fields.filter(function (field) {
          return inspectorControlTypes.indexOf(field.control) === -1;
        });

        return createElement(
          Fragment,
          null,
          inspectorFields.length > 0 &&
            createElement(
              InspectorControls,
              null,
              createElement(
                PanelBody,
                { title: i18n.__('Settings', 'nycbedtoday-blocks'), initialOpen: true },
                inspectorFields.map(function (field) {
                  return createElement(
                    'div',
                    { key: field.name, className: 'nycbedtoday-block-editor__field' },
                    fieldControl(field, attributes, setAttributes)
                  );
                })
              )
            ),
          createElement(
            'div',
            blockProps,
            createElement(
              'div',
              { className: 'nycbedtoday-block-editor__fields' },
              canvasFields.map(function (field) {
                return createElement(
                  'div',
                  { key: field.name, className: 'nycbedtoday-block-editor__field' },
                  fieldControl(field, attributes, setAttributes)
                );
              })
            )
          )
        );
      },
      save: function () {
        return null;
      },
    });
  });
})(window, window.wp);
