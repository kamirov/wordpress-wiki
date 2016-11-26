function [status, response] = upload_file(filepath, article_title, title, description)
% Temporarily disabled
% Uploads a file to an article on the wiki
% Inputs:
%     filepath          Relative or absolute file path
%     article_title     Article title
%     title             File title
%     description       Optional file description
% Outputs:
%     status            Boolean. True if upload successful.
%     response          Server response (useful for debugging).

error('Function disabled. For now use the WP interface to upload files to articles (until we can get this function working.');

if nargin < 4
    description = '';
end

f = fopen(filepath, 'r');
data = fread(f);
encoded = base64encode(data);
fclose(f);

[~, name, ext] = fileparts(filepath); 

write_data = struct('attachment_ext', ext, ...
                    'encoded_attachment', encoded, ...
                    'article_title', article_title, ...
                    'title', title, ...
                    'description', description, ...
                    'filename', name);

write_data_json = savejson(write_data);

response = webwrite(matlab_wiki.url, matlab_wiki.opts, 'write_data', write_data_json, ...
                                                       'from_matlab', 1, ...
                                                       'write_type', 'attachment');
if (strcmp(response, '1'))
    status = true;
else
    status = false;
end
