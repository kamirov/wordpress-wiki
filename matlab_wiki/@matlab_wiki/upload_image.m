function [status, response] = upload_image(fig, article_title, title, description, caption)
% Uploads an image to an article on the wiki (Wordpress)
% Inputs:
%     fig               figure handle or image file path
%     article_title     Article title
%     title             Image title
%     description       Optional image description
%     caption           Optional image caption
% Outputs:
%     status            Boolean. True if upload successful.
%     response          Server response (useful for debugging).

if nargin < 5 || isempty(caption)
    caption = '';
end

if nargin < 4 || isempty(description)
    description = '';
end

if nargin < 3 || isempty(title)
    % We could let titles be optional and let people just call the function
    % with no arguments to upload the current figure with a randomized
    % name. It's more convenient, but might result in a large collection of
    % untitled figures on the server that we later need to sift through to
    % "find that one image I uploaded that one time".
    error('Image needs a title.');
end

if nargin < 2 || isempty(article_title)
    error('Need an article title.');
end

if nargin < 1 || isempty(fig)
    error('No image passed.');    
end

%% Encode the image
fig_is_file = ischar(fig);    

if fig_is_file
    tmp_filename = fig;
else
    % Save tmp image file with random name
    random_string = char(randi([97 122], [1 10]));  % 97-122 is a-z
    print(random_string, '-dpng');
    tmp_filename = [random_string '.png'];
end

% Open and encode image as PNG
f = fopen(tmp_filename, 'r');
img_data = fread(f);
encoded_img = base64encode(img_data);
fclose(f);

% Delete tmp image file
if ~fig_is_file
    delete(tmp_filename);
end

write_data = struct('attachment_ext', '.png', ...
                    'encoded_attachment', encoded_img, ...
                    'article_title', article_title, ...
                    'title', title, ...
                    'description', description, ...
                    'caption', caption);

write_data_json = savejson(write_data);

response = webwrite(matlab_wiki.url, matlab_wiki.opts, 'write_data', write_data_json, ...
                                                       'from_matlab', 1, ...
                                                       'write_type', 'attachment');
if (strcmp(response, '1'))
    status = true;
else
    status = false;
end