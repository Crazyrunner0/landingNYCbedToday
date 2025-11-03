import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { useState } from '@wordpress/element';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { items } = attributes;
  const [nextId, setNextId] = useState(
    items.length > 0 ? Math.max(...items.map((i) => i.id)) + 1 : 1
  );

  const blockProps = useBlockProps({
    className: 'social-proof-strip',
  });

  const addItem = () => {
    setAttributes({
      items: [
        ...items,
        {
          id: nextId,
          text: 'Add proof text here',
          icon: 'star',
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
      <div className="social-proof-items">
        {items.map((item) => (
          <div key={item.id} className="social-proof-item">
            <RichText
              value={item.text}
              onChange={(value) => updateItem(item.id, 'text', value)}
              placeholder="Enter proof text"
            />
            <Button isDestructive onClick={() => removeItem(item.id)}>
              Remove
            </Button>
          </div>
        ))}
      </div>
      <Button isPrimary onClick={addItem}>
        Add Proof Item
      </Button>
    </div>
  );
};

const save = (props) => {
  const { attributes } = props;
  const { items } = attributes;

  const blockProps = useBlockProps.save({
    className: 'social-proof-strip',
  });

  return (
    <div {...blockProps}>
      <div className="social-proof-items">
        {items.map((item) => (
          <div key={item.id} className="social-proof-item">
            <span className="social-proof-text">{item.text}</span>
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
