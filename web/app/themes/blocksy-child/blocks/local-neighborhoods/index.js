import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { title, description, neighborhoods } = attributes;
  const [nextId, setNextId] = useState(
    neighborhoods.length > 0 ? Math.max(...neighborhoods.map((n) => n.id)) + 1 : 1
  );

  const blockProps = useBlockProps({
    className: 'local-neighborhoods',
  });

  const addNeighborhood = () => {
    setAttributes({
      neighborhoods: [
        ...neighborhoods,
        {
          id: nextId,
          name: 'New Area',
          description: 'Add description here',
        },
      ],
    });
    setNextId(nextId + 1);
  };

  const updateNeighborhood = (id, field, value) => {
    setAttributes({
      neighborhoods: neighborhoods.map((neighborhood) =>
        neighborhood.id === id ? { ...neighborhood, [field]: value } : neighborhood
      ),
    });
  };

  const removeNeighborhood = (id) => {
    setAttributes({
      neighborhoods: neighborhoods.filter((neighborhood) => neighborhood.id !== id),
    });
  };

  return (
    <div {...blockProps}>
      <RichText
        tagName="h2"
        className="local-neighborhoods-title"
        value={title}
        onChange={(value) => setAttributes({ title: value })}
        placeholder="Enter section title"
      />

      <RichText
        tagName="p"
        className="local-neighborhoods-description"
        value={description}
        onChange={(value) => setAttributes({ description: value })}
        placeholder="Enter description"
      />

      <div className="neighborhoods-grid">
        {neighborhoods.map((neighborhood) => (
          <div key={neighborhood.id} className="neighborhood-item">
            <TextControl
              label="Neighborhood Name"
              value={neighborhood.name}
              onChange={(value) => updateNeighborhood(neighborhood.id, 'name', value)}
            />
            <TextControl
              label="Description"
              value={neighborhood.description}
              onChange={(value) => updateNeighborhood(neighborhood.id, 'description', value)}
            />
            <Button isDestructive onClick={() => removeNeighborhood(neighborhood.id)}>
              Remove
            </Button>
          </div>
        ))}
      </div>

      <Button isPrimary onClick={addNeighborhood}>
        Add Neighborhood
      </Button>
    </div>
  );
};

const save = (props) => {
  const { attributes } = props;
  const { title, description, neighborhoods } = attributes;

  const blockProps = useBlockProps.save({
    className: 'local-neighborhoods',
  });

  return (
    <div {...blockProps}>
      <h2 className="local-neighborhoods-title">{title}</h2>
      <p className="local-neighborhoods-description">{description}</p>

      <div className="neighborhoods-grid">
        {neighborhoods.map((neighborhood) => (
          <div key={neighborhood.id} className="neighborhood-item">
            <h3 className="neighborhood-name">{neighborhood.name}</h3>
            <p className="neighborhood-description">{neighborhood.description}</p>
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
