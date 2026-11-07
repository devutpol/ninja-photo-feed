import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { SelectControl } from '@wordpress/components';
import { useBlockProps } from '@wordpress/block-editor';
import { createElement } from '@wordpress/element';

registerBlockType("ninja-photo-feed/feed", {
  apiVersion: 3,
  title: __("Ninja Photo Feed"),
  icon: 'format-gallery',
  category: "formatting",
  keywords: [
    __("Ninja Photo Feed"),
    __("instagram feed"),
    __("photo feed"),
    __("npfeed-gutenberg-block")
  ],
  attributes: {
    templateId: {
      type: "string"
    },
    platformName: {
      type: "string",
      default: "instagram"
    }
  },
  edit({ attributes, setAttributes }) {
    const config = window.npfeed_block_vars;
    const blockProps = useBlockProps({ className: 'npfeed-guten-wrapper' });
    const changeSelect = function(templateId) {
      var platformName = config.templates.filter(function(item) {
        return parseInt(item.id) === parseInt(templateId);
      })[0].platform;
      setAttributes({ templateId });
      setAttributes({ platformName });
    };

    return createElement(
      'div',
      blockProps,
      createElement(
        'div',
        { className: 'npfeed-logo' },
        createElement('img', { src: config.logo, alt: 'Ninja Photo Feed' })
      ),
      createElement(SelectControl, {
        label: __("Please Select a template"),
        value: attributes.templateId,
        options: config.templates.map(template => ({
          value: template.id,
          label: template.title
        })),
        onChange: templateId => changeSelect(templateId)
      })
    );
  },
  save({ attributes }) {
    return `[ninja_photo_feed id=${attributes.templateId} platform='${attributes.platformName}']`;
  }
});