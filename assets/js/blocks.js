/**
 * Block editor: the "Tournament Table" and "Matches" blocks.
 *
 * Both blocks pick one saved post; everything else comes from that post's
 * settings. Per-block names, labels and AJAX details are localized by PHP
 * as window.mtrnBlocks.
 *
 * @package MeinTurnierplan
 * @since   1.4.0
 */

(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, useState, useEffect } = wp.element;
  const { SelectControl, Placeholder, Spinner } = wp.components;
  const { useBlockProps } = wp.blockEditor || {};
  const blocks = window.mtrnBlocks || {};

  const icon = el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
    el('path', {
      d: 'M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h10v2H7v-2z',
      fill: 'currentColor'
    })
  );

  Object.keys(blocks).forEach(function(type) {
    const block = blocks[type];

    registerBlockType(block.name, {
      title: block.title,
      icon: icon,
      category: 'widgets',
      description: block.description,

      attributes: {
        tableId: { type: 'string', default: '' },
        tableName: { type: 'string', default: '' }
      },

      edit: function(props) {
        const { attributes, setAttributes } = props;
        const { tableId } = attributes;
        const [posts, setPosts] = useState([]);
        const [loading, setLoading] = useState(true);
        const blockProps = useBlockProps ? useBlockProps() : {};

        // Load the saved posts of this type once
        useEffect(() => {
          const formData = new FormData();
          formData.append('action', block.action);
          formData.append('nonce', block.nonce);

          fetch(block.ajaxUrl, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                setPosts(data.data);
              }
              setLoading(false);
            })
            .catch(error => {
              console.error('Error fetching posts:', error);
              setLoading(false);
            });
        }, []);

        const onChange = function(value) {
          const selected = posts.find(post => String(post.value) === String(value));
          setAttributes({ tableId: value, tableName: selected ? selected.label : '' });
        };

        if (loading) {
          return el('div', blockProps,
            el('div', { className: 'components-placeholder' },
              el(Placeholder, { icon: icon, label: block.title }, el(Spinner))
            )
          );
        }

        return el('div', blockProps,
          el(Placeholder, { icon: icon, label: block.title },
            el(SelectControl, {
              label: block.selectLabel,
              value: tableId,
              options: posts,
              onChange: onChange
            })
          )
        );
      },

      // Dynamic block: rendered by PHP
      save: function() {
        return null;
      }
    });
  });

})(window.wp);
