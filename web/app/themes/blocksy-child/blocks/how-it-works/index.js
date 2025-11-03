import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText } from '@wordpress/block-editor';
import { Button, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { title, steps } = attributes;
  const [nextId, setNextId] = useState(
    steps.length > 0 ? Math.max(...steps.map((s) => s.id)) + 1 : 1
  );

  const blockProps = useBlockProps({
    className: 'how-it-works',
  });

  const addStep = () => {
    const newNumber = (steps.length + 1).toString();
    setAttributes({
      steps: [
        ...steps,
        {
          id: nextId,
          number: newNumber,
          title: 'New Step',
          description: 'Add description here',
        },
      ],
    });
    setNextId(nextId + 1);
  };

  const updateStep = (id, field, value) => {
    setAttributes({
      steps: steps.map((step) => (step.id === id ? { ...step, [field]: value } : step)),
    });
  };

  const removeStep = (id) => {
    setAttributes({
      steps: steps.filter((step) => step.id !== id),
    });
  };

  return (
    <div {...blockProps}>
      <RichText
        tagName="h2"
        className="how-it-works-title"
        value={title}
        onChange={(value) => setAttributes({ title: value })}
        placeholder="Enter section title"
      />

      <div className="how-it-works-steps">
        {steps.map((step, index) => (
          <div key={step.id} className="how-it-works-step">
            <TextControl
              label="Step Number"
              value={step.number}
              onChange={(value) => updateStep(step.id, 'number', value)}
            />
            <TextControl
              label="Title"
              value={step.title}
              onChange={(value) => updateStep(step.id, 'title', value)}
            />
            <TextControl
              label="Description"
              value={step.description}
              onChange={(value) => updateStep(step.id, 'description', value)}
            />
            <Button isDestructive onClick={() => removeStep(step.id)}>
              Remove
            </Button>
          </div>
        ))}
      </div>

      <Button isPrimary onClick={addStep}>
        Add Step
      </Button>
    </div>
  );
};

const save = (props) => {
  const { attributes } = props;
  const { title, steps } = attributes;

  const blockProps = useBlockProps.save({
    className: 'how-it-works',
  });

  return (
    <div {...blockProps}>
      <h2 className="how-it-works-title">{title}</h2>

      <div className="how-it-works-steps">
        {steps.map((step) => (
          <div key={step.id} className="how-it-works-step">
            <div className="step-number">{step.number}</div>
            <h3 className="step-title">{step.title}</h3>
            <p className="step-description">{step.description}</p>
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
