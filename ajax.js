jQuery(document).ready(function ($) {
    function getPostContentUntilSelectedBlock(selectedBlock) {
        const blocks = wp.data.select('core/block-editor').getBlocks();
        let postContent = '';

        for (const block of blocks) {
            if (block.clientId === selectedBlock.clientId) {
                break;
            }
            if (block.attributes.content) {
                postContent += block.attributes.content;
            }
        }

        // Limit the text to the last 250 words
        const words = postContent.split(/\s+/);
        if (words.length > 150) {
            postContent = words.slice(words.length - 150).join(' ');
        }

        return postContent;
    }

    let isProcessing = false;

    wp.data.subscribe(() => {
        const selectedBlock = wp.data.select('core/block-editor').getSelectedBlock();

        if (selectedBlock && selectedBlock.name === 'core/paragraph' && !isProcessing) {
            const content = selectedBlock.attributes.content;

            if (content.startsWith('/convurt')) {
                isProcessing = true;
                const postContentUntilSelectedBlock = getPostContentUntilSelectedBlock(selectedBlock);

                wp.data.dispatch('core/block-editor').updateBlockAttributes(selectedBlock.clientId, {
                    content: 'Processing...',
                });

                console.log('Prompt sent to API:', postContentUntilSelectedBlock);

                $.ajax({
                    method: 'POST',
                    url: 'https://api.openai.com/v1/engines/text-davinci-003/completions',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + convurtBlock.apiKey,
                    },
                    data: JSON.stringify({
                        prompt: 'Continue writing this blog post:\n' + postContentUntilSelectedBlock,
                        max_tokens: 600, // Adjust the number of tokens as needed
                        n: 1,
                        stop: null,
                        temperature: 0.8,
                        presence_penalty: 0.1,
                        frequency_penalty: 0.1,
                    }),
                })
                    .done(function (data) {
                        console.log('API response:', data);
                        const generatedText = data.choices[0].text;
                        wp.data.dispatch('core/block-editor').replaceBlocks(selectedBlock.clientId, wp.blocks.createBlock('core/paragraph', {
                            content: generatedText,
                        }));
                        isProcessing = false;
                    })
                    .fail(function (error) {
                        console.error('Error generating content with OpenAI API:', error);
                        isProcessing = false;
                    });
            }
        }
    });
});
