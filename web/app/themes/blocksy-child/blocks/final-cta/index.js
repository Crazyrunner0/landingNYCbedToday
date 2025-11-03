import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, ColorPaletteControl } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const {
    headline,
    description,
    primaryButtonText,
    primaryButtonUrl,
    secondaryButtonText,
    secondaryButtonUrl,
    backgroundColor,
    textColor,
  } = attributes;

  const blockProps = useBlockProps({
    style: {
      backgroundColor,
      color: textColor,
      padding: '60px 40px',
      textAlign: 'center',
    },
  });

  return (
    <div {...blockProps}>
      <div className="final-cta-content">
        <RichText
          tagName="h2"
          className="final-cta-headline"
          value={headline}
          onChange={(value) => setAttributes({ headline: value })}
          placeholder="Enter headline"
        />
        <RichText
          tagName="p"
          className="final-cta-description"
          value={description}
          onChange={(value) => setAttributes({ description: value })}
          placeholder="Enter description"
        />

        <div className="final-cta-buttons">
          <a href={primaryButtonUrl} className="final-cta-button primary">
            {primaryButtonText}
          </a>
          <a href={secondaryButtonUrl} className="final-cta-button secondary">
            {secondaryButtonText}
          </a>
        </div>
      </div>

      <div className="final-cta-controls">
        <TextControl
          label="Primary Button Text"
          value={primaryButtonText}
          onChange={(value) =>
            setAttributes({
              primaryButtonText: value,
            })
          }
        />
        <TextControl
          label="Primary Button URL"
          value={primaryButtonUrl}
          onChange={(value) => setAttributes({ primaryButtonUrl: value })}
        />
        <TextControl
          label="Secondary Button Text"
          value={secondaryButtonText}
          onChange={(value) =>
            setAttributes({
              secondaryButtonText: value,
            })
          }
        />
        <TextControl
          label="Secondary Button URL"
          value={secondaryButtonUrl}
          onChange={(value) => setAttributes({ secondaryButtonUrl: value })}
        />
        <ColorPaletteControl
          label="Background Color"
          value={backgroundColor}
          onChange={(value) => setAttributes({ backgroundColor: value })}
        />
        <ColorPaletteControl
          label="Text Color"
          value={textColor}
          onChange={(value) => setAttributes({ textColor: value })}
        />
      </div>
    </div>
  );
};

const save = (props) => {
  const { attributes } = props;
  const {
    headline,
    description,
    primaryButtonText,
    primaryButtonUrl,
    secondaryButtonText,
    secondaryButtonUrl,
    backgroundColor,
    textColor,
  } = attributes;

  const blockProps = useBlockProps.save({
    style: {
      backgroundColor,
      color: textColor,
      padding: '60px 40px',
      textAlign: 'center',
    },
  });

  return (
    <div {...blockProps}>
      <div className="final-cta-content">
        <h2 className="final-cta-headline">{headline}</h2>
        <p className="final-cta-description">{description}</p>

        <div className="final-cta-buttons">
          <a href={primaryButtonUrl} className="final-cta-button primary">
            {primaryButtonText}
          </a>
          <a href={secondaryButtonUrl} className="final-cta-button secondary">
            {secondaryButtonText}
          </a>
        </div>
      </div>
    </div>
  );
};

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save,
});
