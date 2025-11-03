import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { title, items } = attributes;
  const [nextId, setNextId] = useState(
    items.length > 0 ? Math.max(...items.map((i) => i.id)) + 1 : 1
  );

  const blockProps = useBlockProps({
    className: 'value-stack',
  });

  const addItem = () => {
    setAttributes({
      items: [
        ...items,
        {
          id: nextId,
          title: 'New Value',
          description: 'Add description here',
        },
      ],
    });
    setNextId(nextId + 1);
  };

  const updateItem = (id, field, value) => {
    setAttributes({
      items: items.map((item) => (item.id === id ? { ...item, [field]: value } : item)),
    });
  };

  const removeItem = (id) => {
    setAttributes({
      items: items.filter((item) => item.id !== id),
    });
  };

  return (
    <div {...blockProps}>
      <RichText
        tagName="h2"
        className="value-stack-title"
        value={title}
        onChange={(value) => setAttributes({ title: value })}
        placeholder="Enter section title"
      />

      <div className="value-stack-items">
        {items.map((item) => (
          <div key={item.id} className="value-stack-item">
            <TextControl
              label="Title"
              value={item.title}
              onChange={(value) => updateItem(item.id, 'title', value)}
            />
            <TextControl
              label="Description"
              value={item.description}
              onChange={(value) => updateItem(item.id, 'description', value)}
            />
            <Button isDestructive onClick={() => removeItem(item.id)}>
              Remove
            </Button>
          </div>
        ))}
      </div>

      <Button isPrimary onClick={addItem}>
        Add Value Item
      </Button>
    </div>
  );
};

const save = (props) => {
  const { attributes } = props;
  const { title, items } = attributes;

  const blockProps = useBlockProps.save({
    className: 'value-stack',
  });

  return (
    <div {...blockProps}>
      <h2 className="value-stack-title">{title}</h2>

      <div className="value-stack-items">
        {items.map((item) => (
          <div key={item.id} className="value-stack-item">
            <h3 className="value-item-title">{item.title}</h3>
            <p className="value-item-description">{item.description}</p>
          </div>
        ))}
      </div>
    </div>
  );
};

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save,
});
