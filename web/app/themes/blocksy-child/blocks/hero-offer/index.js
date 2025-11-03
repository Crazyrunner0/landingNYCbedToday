import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, RichText, ColorPaletteControl } from '@wordpress/block-editor';
import { TextControl } from '@wordpress/components';
import metadata from './block.json';
import './editor.css';

const Edit = (props) => {
  const { attributes, setAttributes } = props;
  const { headline, subheadline, description, buttonText, buttonUrl, backgroundColor, textColor } =
    attributes;

  const blockProps = useBlockProps({
    style: {
      backgroundColor,
      color: textColor,
      padding: '80px 40px',
      textAlign: 'center',
    },
  });

  return (
    <div {...blockProps}>
      <div className="hero-offer-content">
        <RichText
          tagName="h1"
          className="hero-offer-headline"
          value={headline}
          onChange={(value) => setAttributes({ headline: value })}
          placeholder="Enter headline"
        />
        <RichText
          tagName="p"
          className="hero-offer-subheadline"
          value={subheadline}
          onChange={(value) => setAttributes({ subheadline: value })}
          placeholder="Enter subheadline"
        />
        <RichText
          tagName="p"
          className="hero-offer-description"
          value={description}
          onChange={(value) => setAttributes({ description: value })}
          placeholder="Enter description"
        />
        <a href={buttonUrl} className="hero-offer-button">
          {buttonText}
        </a>
      </div>

      <div className="hero-offer-controls">
        <TextControl
          label="Button Text"
          value={buttonText}
          onChange={(value) => setAttributes({ buttonText: value })}
        />
        <TextControl
          label="Button URL"
          value={buttonUrl}
          onChange={(value) => setAttributes({ buttonUrl: value })}
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
  const { headline, subheadline, description, buttonText, buttonUrl, backgroundColor, textColor } =
    attributes;

  const blockProps = useBlockProps.save({
    style: {
      backgroundColor,
      color: textColor,
      padding: '80px 40px',
      textAlign: 'center',
    },
  });

  return (
    <div {...blockProps}>
      <div className="hero-offer-content">
        <h1 className="hero-offer-headline">{headline}</h1>
        <p className="hero-offer-subheadline">{subheadline}</p>
        <p className="hero-offer-description">{description}</p>
        <a href={buttonUrl} className="hero-offer-button">
          {buttonText}
        </a>
      </div>
    </div>
  );
};

registerBlockType(metadata.name, {
  ...metadata,
  edit: Edit,
  save,
});
