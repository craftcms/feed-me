import {escapeHtml} from 'markdown-it/lib/common/utils';

/**
 * Wraps all inline code snippets in `v-pre` to prevent interpolation of Twig.
 */
function renderInlineCode(tokens, idx, options, env, renderer) {
  var token = tokens[idx];

  return `<code v-pre ${renderer.renderAttrs(token)}>${escapeHtml(
    tokens[idx].content,
  )}</code>`;
}

export {renderInlineCode};
