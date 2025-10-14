(function(wp) {
  const { registerBlockType } = wp.blocks;
  const { createElement: el, Fragment, useState, useEffect } = wp.element;
  const { SelectControl, Placeholder, Spinner } = wp.components;
  const { useBlockProps } = wp.blockEditor || {};
  const { __ } = wp.i18n;
  const { apiFetch } = wp;

  registerBlockType('meinturnierplan/tournament-table', {
    title: __('Tournament Table', 'meinturnierplan'),
    icon: el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
      el('path', {
        d: 'M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h10v2H7v-2z',
        fill: 'currentColor'
      })
    ),
    category: 'widgets',
    description: __('Display a tournament table from your custom post types.', 'meinturnierplan'),

    attributes: {
      tableId: {
        type: 'string',
        default: ''
      },
      tableName: {
        type: 'string',
        default: ''
      }
    },

    edit: function(props) {
      const { attributes, setAttributes } = props;
      const { tableId, tableName } = attributes;
      const [tables, setTables] = useState([]);
      const [loading, setLoading] = useState(true);
      const blockProps = useBlockProps ? useBlockProps() : {};

      // Fetch tournament tables on component mount
      useEffect(() => {
        const formData = new FormData();
        formData.append('action', 'mtp_get_tables');
        formData.append('nonce', mtpBlock.nonce);

        fetch(mtpBlock.ajaxUrl, {
          method: 'POST',
          body: formData
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            setTables(data.data);
          }
          setLoading(false);
        })
        .catch(error => {
          console.error('Error fetching tables:', error);
          setLoading(false);
        });
      }, []);

      const onChangeTable = function(value) {
        const selectedTable = tables.find(table => table.value === value);
        setAttributes({
          tableId: value,
          tableName: selectedTable ? selectedTable.label : ''
        });
      };

      if (loading) {
        return el(
          'div',
          blockProps,
          el(
            Placeholder,
            {
              icon: el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
                el('path', {
                  d: 'M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h10v2H7v-2z',
                  fill: 'currentColor'
                })
              ),
              label: __('Tournament Table', 'meinturnierplan')
            },
            el(Spinner)
          )
        );
      }

      // If a table is selected, show a preview box, otherwise show the selector
      if (tableId) {
        return el(
          'div',
          blockProps,
          el('div', null, __('Selected Tournament Table:', 'meinturnierplan')),
          el('div', null, tableName || __('Tournament Table', 'meinturnierplan')),
          el(SelectControl, {
            label: __('Change Tournament Table', 'meinturnierplan'),
            value: tableId,
            options: tables,
            onChange: onChangeTable
          })
        );
      }

      return el(
        'div',
        blockProps,
        el(
          Placeholder,
          {
            icon: el('svg', { width: 24, height: 24, viewBox: '0 0 24 24' },
              el('path', {
                d: 'M3 3h18v18H3V3zm2 2v14h14V5H5zm2 2h10v2H7V7zm0 4h10v2H7v-2zm0 4h10v2H7v-2z',
                fill: 'currentColor'
              })
            ),
            label: __('Tournament Table', 'meinturnierplan')
          },
          el(SelectControl, {
            label: __('Select Tournament Table', 'meinturnierplan'),
            value: tableId,
            options: tables,
            onChange: onChangeTable
          })
        )
      );
    },

    save: function() {
      // Return null since we use dynamic rendering
      return null;
    }
  });

})(window.wp);
